<?php

use Pastell\Mailer\Mailer;
use Pastell\Service\TokenGenerator;
use Pastell\Service\LoginAttemptLimit;
use Pastell\Service\PasswordEntropy;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ConnexionControler extends PastellControler
{
    private const CHANGE_PASSWORD_TOKEN_TTL_IN_SECONDS = 1800;

    public function _beforeAction()
    {
    }

    /**
     * @return bool|never
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
        if (!$this->getAuthentification()->isConnected()) {
            $this->redirect('/Connexion/connexion');
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
        $info = $authenticationConnecteur->authenticate(SITE_BASE . "/Connexion/externalAuthentication?id_ce=$id_ce");
        $this->setLastMessage("Authentification avec le login : $info");
        $this->redirect("/Connecteur/edition?id_ce=$id_ce");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws JsonException
     */
    public function externalOIDCInfoAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        /** @var OidcAuthentication $authenticationConnecteur */
        $authenticationConnecteur = $this->getConnecteurFactory()->getConnecteurById($id_ce);
        $info = $authenticationConnecteur->getConnectedUserInfo(
            SITE_BASE . "/Connexion/externalOIDCInfo?id_ce=$id_ce"
        );
        $this->setLastMessage(
            "Propriété de l'utilisateur connecté : <pre>" .
            json_encode($info, JSON_THROW_ON_ERROR + JSON_PRETTY_PRINT) .
            '</pre>'
        );
        $this->redirect("/Connecteur/edition?id_ce=$id_ce");
    }


    /**
     * @param AuthenticationConnecteur|null $authenticationConnecteur
     * @return array|bool|mixed
     * @throws Exception
     */
    public function apiExternalConnexion(
        AuthenticationConnecteur $authenticationConnecteur = null,
        bool $redirect = true
    ) {
        if (is_null($authenticationConnecteur)) {
            $authenticationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');
        }
        if (!$authenticationConnecteur) {
            return false;
        }
        /** @var AuthenticationConnecteur $authenticationConnecteur */
        $redirectUrl = false;
        if ($redirect) {
            $redirectUrl = sprintf(
                '%s/%s?request_uri=%s',
                SITE_BASE,
                $this->getGetInfo()->get('page_request'),
                urlencode($this->getGetInfo()->get('request_uri'))
            );
        }
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
                    'Votre login %s est inconnu sur Pastell (%s) ',
                    $externalSystem,
                    $login
                )
            );
        }

        $verificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Vérification');

        if (!$verificationConnecteur) {
            return $id_u;
        }
        /** @var LDAPVerification $verificationConnecteur */

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

    private function setConnexion($id_u, $external_system = 'UNKNOWN_SYSTEM')
    {
        $infoUtilisateur = $this->getUtilisateur()->getInfo($id_u);
        $login = $infoUtilisateur['login'];
        $this->getJournal()->setId($id_u);
        $nom = $infoUtilisateur['prenom'] . ' ' . $infoUtilisateur['nom'];
        $this->getJournal()->add(
            Journal::CONNEXION,
            $infoUtilisateur['id_e'],
            0,
            'Connecté',
            "$nom s'est connecté via $external_system depuis l'adresse " . $_SERVER['REMOTE_ADDR']
        );
        $this->setSessionInfo($login, $id_u);
    }

    private function setSessionInfo(string $login, int $id_u): void
    {
        $this->getAuthentification()->connexion($login, $id_u);
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function adminAction(): void
    {
        $this->setViewParameter('message_connexion', false);

        $this->setViewParameter(
            'login_page_configuration',
            file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION) ?
                file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION) : ''
        );
        $this->setViewParameter('page', 'connexion');
        $this->setViewParameter('page_title', 'Connexion');
        $this->setViewParameter('request_uri', $this->getGetInfo()->get('request_uri'));
        $this->render('connexion/index.html.twig');
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function connexionAction(): void
    {
        $authentificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');

        if ($authentificationConnecteur) {
            /** @var AuthenticationConnecteur $authentificationConnecteur */
            if ($this->externalConnexion($authentificationConnecteur)) {
                $this->setLastError('');
                $this->redirect($this->getGetInfo()->get('request_uri'));
            }
        }
        $certificatConnexion = $this->getObjectInstancier()->getInstance(CertificatConnexion::class);
        $userId = $certificatConnexion->autoConnect();
        if ($userId) {
            $utilisateurInfo = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class)->getInfo($userId);
            $this->setViewParameter('login', $utilisateurInfo['login']);
        }

        $this->setViewParameter(
            'login_page_configuration',
            file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION) ?
                file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION) : ''
        );
        $this->setViewParameter('page', 'connexion');
        $this->setViewParameter('page_title', 'Connexion');
        $this->setViewParameter('request_uri', $this->getGetInfo()->get('request_uri'));
        $lastMessage = $this->getObjectInstancier()->getInstance(LastError::class);
        $lastMessage->setCssClass('alert-connexion');
        $this->render('connexion/index.html.twig');
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function oublieIdentifiantAction(): void
    {
        $config = false;
        try {
            $config = $this->getConnecteurFactory()->getGlobalConnecteurConfig('message-oublie-identifiant');
        } catch (Exception) {
            /* Nothing to do */
        }

        $this->setViewParameter('config', $config);
        $this->setViewParameter(
            'login_page_configuration',
            file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION) ?
                file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION) : ''
        );
        $this->setViewParameter('page', 'oublie_identifiant');
        $this->setViewParameter('page_title', 'Oubli des identifiants');
        $this->render('connexion/password_reset.html.twig');
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws UnrecoverableException
     */
    public function changementMdpAction(): void
    {
        $recuperateur = $this->getGetInfo();
        $passwordEntropy = $this->getObjectInstancier()->getInstance(PasswordEntropy::class);
        $this->setViewParameter('password_min_entropy', $passwordEntropy->getEntropyForDisplay());
        $this->setViewParameter(
            'login_page_configuration',
            file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION) ?
                file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION) : ''
        );
        $this->setViewParameter('mail_verif_password', $recuperateur->get('mail_verif'));
        $this->setViewParameter('page', 'oublie_identifiant');
        $this->setViewParameter('page_title', 'Oubli des identifiants');
        $this->getId_uFromTokenOrFailed($this->getViewParameterByKey('mail_verif_password'));
        $this->render('connexion/password_update.html.twig');
    }

    public function noDroitAction()
    {
        $this->setViewParameter('page_title', 'Pas de droit');
        $this->setViewParameter('template_milieu', 'ConnexionNoDroit');
        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     */
    public function externalErrorAction()
    {
        $authenticationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');

        $this->setViewParameter('page_title', "Erreur lors de l'authentification");
        $this->setViewParameter('template_milieu', 'ExternalError');
        $this->setViewParameter('externalSystem', "système d'authentification inconnu");

        if ($authenticationConnecteur) {
            /** @var AuthenticationConnecteur $authenticationConnecteur */
            $this->setViewParameter('externalSystem', $authenticationConnecteur->getExternalSystemName());
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

        $authentificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');
        if ($authentificationConnecteur) {
            /** @var AuthenticationConnecteur $authentificationConnecteur */
            $authentificationConnecteur->logout($authentificationConnecteur->getLogoutRedirectUrl());
        }

        $this->redirect('/Connexion/connexion');
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

        $authenticationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Authentification');

        if ($authenticationConnecteur && $login != 'admin') {
            /** @var AuthenticationConnecteur $authenticationConnecteur */
            $this->setLastError(
                sprintf(
                    "Veuillez utiliser le serveur %s pour l'authentification",
                    $authenticationConnecteur->getExternalSystemName()
                )
            );
            $this->redirect($redirect_fail);
        }
        $id_u = $this->getUtilisateurListe()->getUtilisateurByLogin($login);

        if (!$id_u) {
            $this->setLastError('Identifiant ou mot de passe incorrect.');
            $this->redirect($redirect_fail);
        }
        $verificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur('Vérification');

        if ($verificationConnecteur && $login != 'admin') {
            /** @var LDAPVerification $verificationConnecteur */
            if (!$verificationConnecteur->verifLogin($login, $password)) {
                $this->getLastError()->setLastError('Login ou mot de passe incorrect. (LDAP)');
                $this->redirect($redirect_fail);
            }
        } else {
            $loginAttemptLimit = $this->getObjectInstancier()->getInstance(LoginAttemptLimit::class);

            if (false === $loginAttemptLimit->isLoginAttemptAuthorized($login)) {
                $this->getLastError()->setLastError('Trop de tentatives de connexion, veuillez réessayer plus tard.');
                $this->redirect($redirect_fail);
            }
            if (!$this->getUtilisateur()->verifPassword($id_u, $password)) {
                $this->getLastError()->setLastError('Login ou mot de passe incorrect.');
                $this->redirect($redirect_fail);
            }
            $loginAttemptLimit->resetLoginAttempt($login);
        }

        $certificatConnexion = $this->getInstance(CertificatConnexion::class);

        if (!$certificatConnexion->connexionGranted($id_u)) {
            $this->setLastError('Vous devez avoir un certificat valide pour ce compte');
            $this->redirect($redirect_fail);
        }

        $this->getJournal()->setId($id_u);
        $infoUtilisateur = $this->getUtilisateur()->getInfo($id_u);
        $nom = $infoUtilisateur['prenom'] . ' ' . $infoUtilisateur['nom'];
        $this->getJournal()->add(
            Journal::CONNEXION,
            $infoUtilisateur['id_e'],
            0,
            'Connecté',
            "$nom s'est connecté depuis l'adresse " . $_SERVER['REMOTE_ADDR']
        );
        $this->setSessionInfo($login, $id_u);
        return $id_u;
    }

    public function doConnexionAction()
    {
        $this->connexionActionRedirect('Connexion/connexion');
        $request_uri = $this->getPostInfo()->get('request_uri');

        $this->redirect(urldecode($request_uri));
    }

    public function autoConnectAction()
    {
        $certificatConnexion = new CertificatConnexion($this->getSQLQuery());
        $id_u = $certificatConnexion->autoConnect();

        if (!$id_u) {
            $this->redirect('/Connexion/index');
        }

        $utilisateur = new UtilisateurSQL($this->getSQLQuery());
        $utilisateurInfo = $utilisateur->getInfo($id_u);

        $this->getJournal()->setId($id_u);
        $nom = $utilisateurInfo['prenom'] . ' ' . $utilisateurInfo['nom'];
        $this->getJournal()->add(
            Journal::CONNEXION,
            $utilisateurInfo['id_e'],
            0,
            'Connecté',
            "$nom s'est connecté automatiquement depuis l'adresse " . $_SERVER['REMOTE_ADDR']
        );

        $this->setSessionInfo($utilisateurInfo['login'], $id_u);
        $this->redirect();
    }

    private function getId_uFromTokenOrFailed(string $mail_verif_password)
    {
        $utilisateurListe = $this->getObjectInstancier()->getInstance(UtilisateurListe::class);
        $id_u = $utilisateurListe->getByVerifPassword(
            $mail_verif_password,
            self::CHANGE_PASSWORD_TOKEN_TTL_IN_SECONDS
        );

        if (!$id_u) {
            /* Note : on ne peut pas mettre de message d'erreur personnalisé pour le moment */
            echo 'Le lien du mail a expiré. Veuillez recommencer la procédure';
            exit_wrapper();
        }
        return $id_u;
    }

    public function doModifPasswordAction()
    {
        $recuperateur = new Recuperateur($_POST);

        $mail_verif_password = $recuperateur->get('mail_verif_password');
        $password = $recuperateur->get('password');
        $password2 = $recuperateur->get('password2');

        $id_u = $this->getId_uFromTokenOrFailed($mail_verif_password);

        /* Cf issue #1002 le composant graphique Libriciel n'intègre pas encore de message d'erreur personnalisé.*/
        /* Cf également MR #883 */
        if (! $password) {
            /* Note : on ne peut pas mettre de message d'erreur personnalisé pour le moment */
            $this->setLastError('Le mot de passe est obligatoire');
            $this->redirect("/Connexion/changementMdp?mail_verif=$mail_verif_password");
        }
        if ($password != $password2) {
            /* Note : on ne peut pas mettre de message d'erreur personnalisé pour le moment */
            $this->setLastError('Les mots de passe ne correspondent pas');
            $this->redirect("/Connexion/changementMdp?mail_verif=$mail_verif_password");
        }

        $passwordEntropy = $this->getObjectInstancier()->getInstance(PasswordEntropy::class);
        if (! $passwordEntropy->isPasswordStrongEnough($password)) {
            /* Note : on ne peut pas mettre de message d'erreur personnalisé pour le moment */
            $this->setLastError(
                "Le mot de passe n'a pas été changé car le nouveau mot de passe n'est pas assez fort. " .
                "Essayez de l'allonger ou de mettre des caractères de différents types. " .
                'La barre de vérification doit être entièrement remplie.'
            );
            $this->redirect("/Connexion/changementMdp?mail_verif=$mail_verif_password");
        }

        $utilisateur = new UtilisateurSQL($this->getSQLQuery());
        $infoUtilisateur = $utilisateur->getInfo($id_u);
        $utilisateur->setPassword($id_u, $password);

        $utilisateur->reinitPassword($id_u, (new UriSafeTokenGenerator())->generateToken());

        $this->getJournal()->add(
            Journal::MODIFICATION_UTILISATEUR,
            $infoUtilisateur['id_e'],
            0,
            'mot de passe modifié',
            "{$infoUtilisateur['login']} ({$infoUtilisateur['id_u']}) a modifié son mot de passe"
        );

        /* Note : on ne peut pas mettre de message personnalisé pour le moment */
        $this->setLastMessage('Votre mot de passe a été modifié');
        $this->redirect('/Connexion/index');
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function doOublieIdentifiantAction()
    {
        $recuperateur = $this->getPostInfo();

        $login = $recuperateur->get('login');

        $utilisateurListe = new UtilisateurListe($this->getSQLQuery());
        $id_u = $utilisateurListe->getByLoginOrEmail($login, $login);

        if (!$id_u) {
            $this->setLastError("Aucun compte n'a été trouvé avec ces informations");
            $this->redirect('/Connexion/oublieIdentifiant');
        }
        $tokenGenerator = new TokenGenerator();
        $mailVerifPassword = $tokenGenerator->generate();

        $utilisateur = new UtilisateurSQL($this->getSQLQuery());
        $info = $utilisateur->getInfo($id_u);
        $utilisateur->reinitPassword($id_u, $mailVerifPassword);

        $link = sprintf(
            '%s/Connexion/changementMdp?mail_verif=%s',
            SITE_BASE,
            $mailVerifPassword
        );
        $templatedEmail = (new TemplatedEmail())
            ->to($info['email'])
            ->subject('[Pastell] Procédure de modification de mot de passe')
            ->htmlTemplate('oublie-identifiant.html.twig')
            ->context(['link' => $link]);
        $this->getObjectInstancier()
            ->getInstance(Mailer::class)
            ->send($templatedEmail);

        $this->getJournal()->addActionAutomatique(
            Journal::MODIFICATION_UTILISATEUR,
            $info['id_e'],
            0,
            'mot de passe modifié',
            "Procédure initiée pour {$info['email']}"
        );

        $this->setLastMessage('Un email vous a été envoyé avec la suite de la procédure');
        $this->redirect('/Connexion/oublieIdentifiant');
    }

    public function indexAction(): void
    {
        $this->connexionAction();
    }
}
