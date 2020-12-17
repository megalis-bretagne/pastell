<?php

class ConnexionControler extends PastellControler
{

    public function _beforeAction()
    {
    }

    /**
     * @return bool
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function verifConnected()
    {
        if ($this->getAuthentification()->isConnected()) {
            return true;
        }
        try {
            /** @var AuthenticationConnecteur $authenticationConnecteur */
            $authenticationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');
            $id_u = $this->apiExternalConnexion();
            if ($id_u) {
                $this->setConnexion($id_u, $authenticationConnecteur->getExternalSystemName());
            }
        } catch (Exception $e) {
        }

        if (! $this->getAuthentification()->isConnected()) {
            $this->redirect("/Connexion/connexion");
        }

        return false;
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function externalAuthenticationAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        /** @var AuthenticationConnecteur $authenticationConnecteur */
        $authenticationConnecteur = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $login = $authenticationConnecteur->authenticate(SITE_BASE . "/Connexion/externalAuthentication?id_ce=$id_ce");
        $this->setLastMessage("Authentification avec le login : $login");
        $this->redirect("/Connecteur/edition?id_ce=$id_ce");
    }

    /**
     * @deprecated 3.0.0 Cette partie est dépréciée et n'est utile qu'avec pastell-compat-v2 et sera retiré dans une prochaine version mineur
     */
    public function openIdReturnAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $state = $recuperateur->get('state');
        $state = urldecode($state);
        $state_array = array();
        parse_str($state, $state_array);
        $id_ce = $state_array['id_ce'];
        /** @var OpenIDAuthentication $openIdAuthentication */
        $openIdAuthentication = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        if (!$openIdAuthentication) {
            $this->redirect();
        }
        $sub = false;
        try {
            $sub = $openIdAuthentication->returnAuthenticate($recuperateur);
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("Connexion/connexion");
        }

        $id_u = $this->getUtilisateur()->getIdFromLogin($sub);
        if (!$id_u) {
            $this->setLastError("Aucun utilisateur ne correspond au login $sub");
            $this->redirect("Connexion/connexion");
        }

        $_SESSION['open_id_authenticate_id_ce'] = $id_ce;

        $this->setConnexion($id_u, "OpenID");
        $this->redirect();
    }

    /**
     * @param AuthenticationConnecteur|null $authenticationConnecteur
     * @return array|bool|mixed
     * @throws Exception
     */
    public function apiExternalConnexion(AuthenticationConnecteur $authenticationConnecteur = null)
    {
        if (is_null($authenticationConnecteur)) {
            /** @var AuthenticationConnecteur $authenticationConnecteur */
            $authenticationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');
        }
        if (!$authenticationConnecteur) {
            return false;
        }
        $redirectUrl = sprintf(
            "%s/%s?request_uri=%s",
            SITE_BASE,
            $this->getGetInfo()->get('page_request'),
            urlencode($this->getGetInfo()->get('request_uri'))
        );
        $login = $authenticationConnecteur->authenticate($redirectUrl);
        $externalSystem = $authenticationConnecteur->getExternalSystemName();

        if (!$login) {
            throw new Exception(
                sprintf(
                    "Le serveur %s n'a pas donné de login",
                    $externalSystem
                )
            );
        }
        $id_u = $this->getUtilisateurListe()->getUtilisateurByLogin($login);
        if (!$id_u) {
            throw new Exception(
                sprintf(
                    "Votre login %s est inconnu sur Pastell (%s) ",
                    $externalSystem,
                    $login
                )
            );
        }

        /** @var LDAPVerification $verificationConnecteur */
        $verificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Vérification');

        if (!$verificationConnecteur) {
            return $id_u;
        }

        if (!$verificationConnecteur->getEntry($login)) {
            throw new Exception("Vous ne pouvez pas vous connecter car vous êtes inconnu sur l'annuaire LDAP");
        }
        return $id_u;
    }

    /**
     * @param AuthenticationConnecteur $authenticationConnecteur
     * @return array|bool|mixed
     * @throws LastErrorException
     * @throws LastMessageException
     */
    private function externalConnexion(AuthenticationConnecteur $authenticationConnecteur)
    {
        $id_u = false;
        try {
            $id_u = $this->apiExternalConnexion($authenticationConnecteur);
            if (!$id_u) {
                return false;
            }
            $this->setConnexion($id_u, $authenticationConnecteur->getExternalSystemName());
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect('/Connexion/externalError');
        }
        return $id_u;
    }

    private function setConnexion($id_u, $external_system = "UNKNOWN_SYSTEM")
    {
        $infoUtilisateur = $this->getUtilisateur()->getInfo($id_u);
        $login = $infoUtilisateur['login'];
        $this->getJournal()->setId($id_u);
        $nom = $infoUtilisateur['prenom'] . " " . $infoUtilisateur['nom'];
        $this->getJournal()->add(Journal::CONNEXION, $infoUtilisateur['id_e'], 0, "Connecté", "$nom s'est connecté via $external_system depuis l'adresse " . $_SERVER['REMOTE_ADDR']);
        $this->getAuthentification()->connexion($login, $id_u);
    }

    public function adminAction()
    {
        $this->{'message_connexion'} = false;

        $this->{'login_page_configuration'} = file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION)
            ? file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION)
            : '';
        $this->{'page'} = "connexion";
        $this->{'page_title'} = "Connexion";
        $this->{'template_milieu'} = "ConnexionIndex";
        $this->{'request_uri'} = $this->getGetInfo()->get('request_uri');
        $this->render('PageConnexion');
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function connexionAction()
    {
        /** @var AuthenticationConnecteur $authentificationConnecteur */
        $authentificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');
        if ($authentificationConnecteur && $this->externalConnexion($authentificationConnecteur)) {
            $this->setLastError('');
            $this->redirect($this->getGetInfo()->get('request_uri'));
        }

        $this->{'login_page_configuration'} = file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION)
            ? file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION)
            : '';
        $this->{'page'} = "connexion";
        $this->{'page_title'} = "Connexion";
        $this->{'template_milieu'} = "ConnexionIndex";
        $this->{'request_uri'} = $this->getGetInfo()->get('request_uri');
        /** @var LastMessage $lastMessage */
        $lastMessage = $this->getObjectInstancier()->getInstance(LastError::class);
        $lastMessage->setCssClass('alert-connexion');
        $this->render('PageConnexion');
    }

    public function oublieIdentifiantAction()
    {

        $config = false;
        try {
            $config = $this->getConnecteurFactory()->getGlobalConnecteurConfig('message-oublie-identifiant');
        } catch (Exception $e) {
            /* Nothing to do */
        }


        $this->{'config'} = $config;
        $this->{'login_page_configuration'} = file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION)
            ? file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION)
            : '';
        $this->{'page'} = "oublie_identifiant";
        $this->{'page_title'} = "Oubli des identifiants";
        $this->{'template_milieu'} = "ConnexionOublieIdentifiant";
        $this->render("PageConnexion");
    }

    public function changementMdpAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $this->{'login_page_configuration'} = file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION)
            ? file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION)
            : '';
        $this->{'mail_verif_password'} = $recuperateur->get('mail_verif');
        $this->{'page'} = "oublie_identifiant";
        $this->{'page_title'} = "Oubli des identifiants";
        $this->{'template_milieu'} = "ConnexionChangementMdp";
        $this->render("PageConnexion");
    }

    public function noDroitAction()
    {
        $this->{'page_title'} = "Pas de droit";
        $this->{'template_milieu'} = "ConnexionNoDroit";
        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     */
    public function externalErrorAction()
    {
        /** @var AuthenticationConnecteur $authenticationConnecteur */
        $authenticationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');

        $this->{'page_title'} = "Erreur lors de l'authentification";
        $this->{'template_milieu'} = "ExternalError";
        $this->{'externalSystem'} = "système d'authentification inconnu";

        if ($authenticationConnecteur) {
            $this->{'externalSystem'} = $authenticationConnecteur->getExternalSystemName();
        }
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function logoutAction()
    {
        $this->getAuthentification()->deconnexion();

        $csrfToken = $this->getInstance(CSRFToken::class);
        $csrfToken->deleteToken();

        /** @var AuthenticationConnecteur $authentificationConnecteur */
        $authentificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');
        if ($authentificationConnecteur) {
            $authentificationConnecteur->logout($authentificationConnecteur->getRedirectUrl());
        }

        if (isset($_SESSION['open_id_authenticate_id_ce'])) {
            /** @deprecated 3.0.0 Cette partie est dépréciée et n'est utile qu'avec pastell-compat-v2 et sera retiré dans une prochaine version mineur **/
            /** @var OpenIDAuthentication $openIdAuthentication */
            $openIdAuthentication = $this->getConnecteurFactory()->getConnecteurById($_SESSION['open_id_authenticate_id_ce']);
            if ($openIdAuthentication) {
                $openIdAuthentication->logout();
            }
        }

        $this->redirect("/Connexion/connexion");
    }

    public function sessionLogoutAction()
    {
        $this->getAuthentification()->deconnexion();
        $this->getInstance(CSRFToken::class)->deleteToken();
    }

    /**
     * @param $redirect_fail
     * @return array|bool|mixed
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws Exception
     */
    public function connexionActionRedirect($redirect_fail)
    {
        $recuperateur = $this->getPostInfo();
        $login = $recuperateur->get('login');
        $password = $recuperateur->get('password');

        /** @var AuthenticationConnecteur $authenticationConnecteur */
        $authenticationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');

        if ($authenticationConnecteur && $login != 'admin') {
            $this->setLastError(
                sprintf(
                    "Veuillez utiliser le serveur %s pour l'authentification",
                    $authenticationConnecteur->getExternalSystemName()
                )
            );
            $this->redirect($redirect_fail);
        }
        $id_u = $this->getUtilisateurListe()->getUtilisateurByLogin($login);

        if (! $id_u) {
            $this->setLastError("Login ou mot de passe incorrect.");
            $this->redirect($redirect_fail);
        }
        /** @var LDAPVerification $verificationConnecteur */
        $verificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur("Vérification");

        if ($verificationConnecteur && $login != 'admin') {
            if (! $verificationConnecteur->verifLogin($login, $password)) {
                $this->getLastError()->setLastError("Login ou mot de passe incorrect. (LDAP)");
                $this->redirect($redirect_fail);
            }
        } else {
            if (!$this->getUtilisateur()->verifPassword($id_u, $password)) {
                $this->getLastError()->setLastError("Login ou mot de passe incorrect.");
                $this->redirect($redirect_fail);
            }
        }

        $certificatConnexion = $this->getInstance(CertificatConnexion::class);

        if (! $certificatConnexion->connexionGranted($id_u)) {
            $this->setLastError("Vous devez avoir un certificat valide pour ce compte");
            $this->redirect($redirect_fail);
        }

        $this->getJournal()->setId($id_u);
        $infoUtilisateur = $this->getUtilisateur()->getInfo($id_u);
        $nom = $infoUtilisateur['prenom'] . " " . $infoUtilisateur['nom'];
        $this->getJournal()->add(Journal::CONNEXION, $infoUtilisateur['id_e'], 0, "Connecté", "$nom s'est connecté depuis l'adresse " . $_SERVER['REMOTE_ADDR']);
        $this->getAuthentification()->connexion($login, $id_u);
        return $id_u;
    }

    public function doConnexionAction()
    {
        $this->connexionActionRedirect("Connexion/connexion");
        $request_uri = $this->getPostInfo()->get('request_uri');

        $this->redirect(urldecode($request_uri));
    }

    public function renderOasisErrorAction()
    {
        $this->{'page'} = "connexion";
        $this->{'page_title'} = "Erreur";
        $this->{'template_milieu'} = "ConnexionOasisError";
        $this->renderDefault();
    }

    public function autoConnectAction()
    {
        $certificatConnexion = new CertificatConnexion($this->getSQLQuery());
        $id_u = $certificatConnexion->autoConnect();

        if (! $id_u) {
            $this->redirect("/Connexion/index");
        }

        $utilisateur = new UtilisateurSQL($this->getSQLQuery());
        $utilisateurInfo = $utilisateur->getInfo($id_u);

        $this->getJournal()->setId($id_u);
        $nom = $utilisateurInfo['prenom'] . " " . $utilisateurInfo['nom'];
        $this->getJournal()->add(Journal::CONNEXION, $utilisateurInfo['id_e'], 0, "Connecté", "$nom s'est connecté automatiquement depuis l'adresse " . $_SERVER['REMOTE_ADDR']);


        $this->getAuthentification()->connexion($utilisateurInfo['login'], $id_u);
        $this->redirect();
    }

    public function doModifPasswordAction()
    {
        $recuperateur = new Recuperateur($_POST);

        $mail_verif_password = $recuperateur->get('mail_verif_password');
        $password = $recuperateur->get('password');
        $password2 = $recuperateur->get('password2');

        $utilisateurListe = new UtilisateurListe($this->getSQLQuery());
        $id_u = $utilisateurListe->getByVerifPassword($mail_verif_password);

        if (! $id_u) {
            /* Note : on ne peut pas mettre de message d'erreur personnalisé pour le moment */
            echo "Le lien du mail a expiré. Veuillez recommencer la procédure";
            exit_wrapper();
        }

        if (! $password) {
            /* Note : on ne peut pas mettre de message d'erreur personnalisé pour le moment */
            $this->setLastError("Le mot de passe est obligatoire");
            $this->redirect("/Connexion/changementMdp?mail_verif=$mail_verif_password");
        }
        if ($password != $password2) {
            /* Note : on ne peut pas mettre de message d'erreur personnalisé pour le moment */
            $this->setLastError("Les mots de passe ne correspondent pas");
            $this->redirect("/Connexion/index");
            $this->redirect("/Connexion/changementMdp?mail_verif=$mail_verif_password");
            exit;
        }


        $utilisateur = new UtilisateurSQL($this->getSQLQuery());
        $infoUtilisateur = $utilisateur->getInfo($id_u);
        $utilisateur->setPassword($id_u, $password);

        $passwordGenerator = new PasswordGenerator();
        $mailVerifPassword = $passwordGenerator->getPassword();
        $utilisateur->reinitPassword($id_u, $mailVerifPassword);

        $this->getJournal()->add(Journal::MODIFICATION_UTILISATEUR, $infoUtilisateur['id_e'], 0, "mot de passe modifié", "{$infoUtilisateur['login']} ({$infoUtilisateur['id_u']}) a modifié son mot de passe");

        /* Note : on ne peut pas mettre de message d'erreur personnalisé pour le moment */
        $this->setLastMessage("Votre mot de passe a été modifié");
        $this->redirect("/Connexion/index");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws Exception
     */
    public function doOublieIdentifiantAction()
    {
        $recuperateur = $this->getPostInfo();

        $login = $recuperateur->get('login');

        $utilisateurListe = new UtilisateurListe($this->getSQLQuery());
        $id_u = $utilisateurListe->getByLoginOrEmail($login, $login);

        if (!$id_u) {
            $this->setLastError("Aucun compte n'a été trouvé avec ces informations");
            $this->redirect("/Connexion/oublieIdentifiant");
        }
        $passwordGenerator = new PasswordGenerator();
        $mailVerifPassword = $passwordGenerator->getPassword();

        $utilisateur = new UtilisateurSQL($this->getSQLQuery());
        $info = $utilisateur->getInfo($id_u);
        $utilisateur->reinitPassword($id_u, $mailVerifPassword);

        /** @var ZenMail $zenMail */
        $zenMail = $this->getInstance(ZenMail::class);
        $zenMail->setEmetteur("Pastell", PLATEFORME_MAIL);
        $zenMail->setDestinataire($info['email']);
        $zenMail->setSujet("[Pastell] Procédure de modification de mot de passe");
        $infoMessage = array('mail_verif_password' => $mailVerifPassword);
        $zenMail->setContenu(PASTELL_PATH . "/mail/changement-mdp.php", $infoMessage);
        $zenMail->send();

        $this->getJournal()->addActionAutomatique(
            Journal::MODIFICATION_UTILISATEUR,
            $info['id_e'],
            0,
            'mot de passe modifié',
            "Procédure initiée pour {$info['email']}"
        );

        $this->setLastMessage("Un email vous a été envoyé avec la suite de la procédure");
        $this->redirect("/Connexion/oublieIdentifiant");
    }

    public function indexAction()
    {
        return $this->connexionAction();
    }
}
