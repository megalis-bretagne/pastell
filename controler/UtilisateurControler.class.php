<?php

use Pastell\Service\PasswordEntropy;

class UtilisateurControler extends PastellControler
{
    /**
     * @return UtilisateurNewEmailSQL
     */
    public function getUtilisateurNewEmailSQL()
    {
        return $this->getInstance(UtilisateurNewEmailSQL::class);
    }

    /**
     * @return NotificationMail
     */
    public function getNotificationMail()
    {
        return $this->getInstance(NotificationMail::class);
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->getInstance(Notification::class);
    }

    public function _beforeAction()
    {
        parent::_beforeAction();
        $id_u = $this->getGetInfo()->getInt('id_u');
        if ($id_u) {
            $info = $this->getUtilisateur()->getInfo($id_u);
            $this->{'id_e'} = $info['id_e'];
            $this->{'id_e_menu'} = $info['id_e'];
            $this->{'type_e_menu'} = "";
            $this->hasDroitLecture($info['id_e']);
            $this->setNavigationInfo($info['id_e'], "Entite/utilisateur?");
        } elseif ($this->getGetInfo()->get('id_e')) {
            $this->{'type_e_menu'} = "";
            $this->{'id_e'} = $this->getGetInfo()->get('id_e');
            $this->{'id_e_menu'} = $this->getGetInfo()->get('id_e');
            $this->setNavigationInfo($this->{'id_e'}, "Entite/utilisateur?");
        } else {
            $this->{'id_e'} = 0;
            $this->setNavigationInfo(0, "Entite/utilisateur?");
        }
        $this->{'menu_gauche_template'} = "EntiteMenuGauche";
        $this->{'menu_gauche_select'} = "Entite/utilisateur";
        $this->setDroitLectureOnConnecteur($this->{'id_e'});
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function modifPasswordAction()
    {
        $authentificationConnecteur = $this->getConnecteurFactory()->getGlobalConnecteur("authentification");
        if ($authentificationConnecteur) {
            $this->{'LastError'}->setLastError("Vous ne pouvez pas modifier votre mot de passe en dehors du CAS");
            $this->redirect("/Utilisateur/moi");
        }
        $this->{'pages_without_left_menu'} = true;

        $this->{'page_title'} = "Modification de votre mot de passe";
        $this->{'template_milieu'} = "UtilisateurModifPassword";
        $passwordEntropy = $this->getObjectInstancier()->getInstance(PasswordEntropy::class);
        $this->{'password_min_entropy'} = $passwordEntropy->getEntropyForDisplay();
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function modifEmailAction()
    {
        $this->{'utilisateur_info'} = $this->getUtilisateur()->getInfo($this->getId_u());
        if ($this->{'utilisateur_info'}['id_e'] == 0) {
            $this->{'LastError'}->setLastError("Les utilisateurs de l'entité racine ne peuvent pas utiliser cette procédure");
            $this->redirect("/Utilisateur/moi");
        }
        $this->{'page_title'} = "Modification de votre email";
        $this->{'template_milieu'} = "UtilisateurModifEmail";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws Exception
     */
    public function modifEmailControlerAction()
    {
        $recuperateur = new Recuperateur($_POST);
        $password = $recuperateur->get('password');
        if (!$this->getUtilisateur()->verifPassword($this->getId_u(), $password)) {
            $this->{'LastError'}->setLastError("Le mot de passe est incorrect.");
            $this->redirect("/Utilisateur/modifEmail");
        }
        $email = $recuperateur->get('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->{'LastError'}->setLastError("L'email que vous avez saisi ne semble pas être valide");
            $this->redirect("/Utilisateur/modifEmail");
        }

        $utilisateur_info = $this->getUtilisateur()->getInfo($this->getId_u());


        $password = $this->getUtilisateurNewEmailSQL()->add($this->getId_u(), $email);

        $zenMail = $this->getZenMail();
        $zenMail->setEmetteur("Pastell", PLATEFORME_MAIL);
        $zenMail->setDestinataire($email);
        $zenMail->setSujet("Changement de mail sur Pastell");
        $info = array("password" => $password);
        $zenMail->setContenu(PASTELL_PATH . "/mail/changement-email.php", $info);
        $zenMail->send();

        $this->getJournal()->add(Journal::MODIFICATION_UTILISATEUR, $utilisateur_info['id_e'], 0, "change-email", "Demande de changement d'email initiée {$utilisateur_info['email']} -> $email");

        $this->setLastMessage("Un email a été envoyé à votre nouvelle adresse. Merci de le consulter pour la suite de la procédure.");
        $this->redirect("/Utilisateur/moi");
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function modifEmailConfirmAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $password = $recuperateur->get('password');
        $info = $this->getUtilisateurNewEmailSQL()->confirm($password);
        if ($info) {
            $this->createChangementEmail($info['id_u'], $info['email']);
        }

        $this->getUtilisateurNewEmailSQL()->delete($info['id_u']);
        $this->{'result'} = $info;
        $this->{'page_title'} = "Procédure de changement d'email";
        $this->{'template_milieu'} = "UtilisateurModifEmailConfirm";
        $this->renderDefault();
    }

    /**
     * @param $id_u
     * @param $email
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    private function createChangementEmail($id_u, $email)
    {
        $utilisateur_info = $this->getUtilisateur()->getInfo($id_u);

        $documentCreationService = $this->getObjectInstancier()->getInstance(DocumentCreationService::class);
        $id_d = $documentCreationService->createDocument($utilisateur_info['id_e'], $id_u, 'changement-email');
        $this->getDocument()->setTitre($id_d, $utilisateur_info['login']);

        /** @var DonneesFormulaire $donneesFormulaire */
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        foreach (array('id_u', 'login', 'nom', 'prenom') as $key) {
            $data[$key] = $utilisateur_info[$key];
        }
        $data['email_actuel'] = $utilisateur_info['email'];
        $data['email_demande'] = $email;
        $donneesFormulaire->setTabData($data);

        $this->getNotificationMail()->notify($utilisateur_info['id_e'], $id_d, 'creation', 'changement-email', $utilisateur_info['login'] . " a fait une demande de changement d'email");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function certificatAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $this->{'verif_number'} = $recuperateur->get('verif_number');
        $this->{'offset'} = $recuperateur->getInt('offset', 0);

        $this->{'limit'} = 20;

        $this->{'count'} = $this->getUtilisateurListe()->getNbUtilisateurByCertificat($this->{'verif_number'});
        $this->{'liste'} = $this->getUtilisateurListe()->getUtilisateurByCertificat($this->{'verif_number'}, $this->{'offset'}, $this->{'limit'});

        if (!$this->{'count'}) {
            $this->redirect("/index.php");
        }

        $this->{'certificat'} = new Certificat($this->{'liste'}[0]['certificat']);
        $this->{'certificatInfo'} = $this->{'certificat'}->getInfo();

        $this->{'page_title'} = "Certificat";
        $this->{'template_milieu'} = "UtilisateurCertificat";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function editionAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_u = $recuperateur->get('id_u');
        $id_e = $recuperateur->getInt('id_e');

        $infoUtilisateur = array('login' => $this->getLastError()->getLastInput('login'),
            'nom' => $this->getLastError()->getLastInput('nom'),
            'prenom' => $this->getLastError()->getLastInput('prenom'),
            'email' => $this->getLastError()->getLastInput('email'),
            'certificat' => '',
            'id_e' => $id_e,
        );

        if ($id_u) {
            $infoUtilisateur = $this->getUtilisateur()->getInfo($id_u);
            if (!$infoUtilisateur) {
                $this->redirect();
            }
        }

        $this->verifDroit($infoUtilisateur['id_e'], "utilisateur:edition");

        $this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($infoUtilisateur['id_e']);
        $this->{'certificat'} = new Certificat($infoUtilisateur['certificat']);
        $this->{'arbre'} = $this->getRoleUtilisateur()->getArbreFille($this->getId_u(), "entite:edition");

        if ($id_u) {
            $this->{'page_title'} = "Modification de " . $infoUtilisateur['prenom'] . " " . $infoUtilisateur['nom'];
        } else {
            $this->{'page_title'} = "Nouvel utilisateur ";
        }
        $this->{'id_u'} = $id_u;
        $this->{'id_e'} = $id_e;
        $this->{'infoUtilisateur'} = $infoUtilisateur;
        $this->{'template_milieu'} = "UtilisateurEdition";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function detailAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_u = $recuperateur->get('id_u');

        $info = $this->getUtilisateur()->getInfo($id_u);
        if (!$info) {
            $this->setLastError("Utilisateur $id_u inconnu");
            $this->redirect("index.php");
        }

        $this->{'certificat'} = new Certificat($info['certificat']);
        $this->{'page_title'} = "Utilisateur " . $info['prenom'] . " " . $info['nom'];
        $this->{'entiteListe'} = $this->getEntiteListe();
        $this->{'tabEntite'} = $this->getRoleUtilisateur()->getEntite($this->getId_u(), 'entite:edition');

        $this->{'notification_list'} = $this->getNotificationList($id_u);
        if ($this->hasDroit($info['id_e'], 'role:lecture')) {
            $this->{'role_authorized'} = $this->apiGet('role');
        } else {
            $this->{'role_authorized'} = array();
        }

        if (!$this->getRoleUtilisateur()->hasDroit($this->getId_u(), "utilisateur:lecture", $info['id_e'])) {
            $this->setLastError("Vous n'avez pas le droit de lecture (" . $info['id_e'] . ")");
            $this->redirect();
        }
        $this->{'utilisateur_edition'} = $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "utilisateur:edition", $info['id_e']);

        if ($info['id_e']) {
            $this->{'infoEntiteDeBase'} = $this->getEntiteSQL()->getInfo($info['id_e']);
            $this->{'denominationEntiteDeBase'} = $this->{'infoEntiteDeBase'}['denomination'];
        }
        $this->{'info'} = $info;
        $this->{'id_u'} = $id_u;
        $this->{'arbre'} = $this->getRoleUtilisateur()->getArbreFille($this->getId_u(), "entite:edition");
        $this->{'template_milieu'} = "UtilisateurDetail";
        $this->renderDefault();
    }

    private function getNotificationList($id_u)
    {
        $result = $this->getNotification()->getAll($id_u);
        foreach ($result as $i => $line) {
            $action = $this->getDocumentTypeFactory()->getFluxDocumentType($line['type'])->getAction();
            foreach ($line['action'] as $j => $action_id) {
                $result[$i]['action'][$j] = $action->getActionName($action_id);
            }
        }
        return $result;
    }

    /**
     * @throws NotFoundException
     */
    public function moiAction()
    {
        $id_u = $this->getId_u();
        $info = $this->getUtilisateur()->getInfo($id_u);
        $this->{'certificat'} = new Certificat($info['certificat']);

        $this->{'page_title'} = "Espace utilisateur : " . $info['prenom'] . " " . $info['nom'];

        $this->{'entiteListe'} = $this->getEntiteListe();

        $this->{'tabEntite'} = $this->getRoleUtilisateur()->getEntite($this->getId_u(), 'entite:edition');

        $this->{'notification_list'} = $this->getNotificationList($id_u);

        $this->{'roleInfo'} = $this->getRoleUtilisateur()->getRole($id_u);
        $this->{'utilisateur_edition'} = $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "utilisateur:edition", $info['id_e']);

        if ($info['id_e']) {
            $infoEntiteDeBase = $this->getEntiteSQL()->getInfo($info['id_e']);
            $this->{'denominationEntiteDeBase'} = $infoEntiteDeBase['denomination'];
        }
        $this->{'info'} = $info;
        $this->{'id_u'} = $id_u;
        $this->{'arbre'} = $this->getRoleUtilisateur()->getArbreFille($this->getId_u(), "entite:lecture");
        $this->{'template_milieu'} = "UtilisateurMoi";
        $this->{'pages_without_left_menu'} = true;
        $this->renderDefault();
    }


    /**
     * Prise en compte du paramètre $message dans l'affectation de l'erreur
     * Correction "lastError"
     * @param $id_e
     * @param $id_u
     * @param $message
     * @throws LastErrorException
     * @throws LastMessageException
     */
    private function redirectEdition($id_e, $id_u, $message)
    {
        $this->setLastError($message);
        $this->redirect("/Utilisateur/edition?id_e=$id_e&id_u=$id_u");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doEditionAction()
    {
        $recuperateur = $this->getPostInfo();

        $id_e = $recuperateur->getInt('id_e');
        $id_u = $recuperateur->get('id_u');

        $password = $recuperateur->get('password');
        $password2 = $recuperateur->get('password2');

        try {
            if ($password && ($password != $password2)) {
                //La vérification du mot de passe ne concerne que la partie web et n'est pas vérifié par l'API
                throw new BadRequestException("Les mots de passe ne correspondent pas");
            }
            if ($id_u) {
                $this->apiPatch("/utilisateur/$id_u");
            } else {
                $result = $this->apiPost("/utilisateur");
                $id_u = $result['id_u'];
            }
        } catch (Exception $e) {
            $this->redirectEdition($id_e, $id_u, $e->getMessage());
        }

        $this->redirect("/Utilisateur/detail?id_u=$id_u");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function ajoutRoleAction()
    {
        $recuperateur = new Recuperateur($_POST);
        $id_u = $recuperateur->get('id_u');
        $role = $recuperateur->get('role');
        $id_e = $recuperateur->get('id_e', 0);

        $this->verifDroit($id_e, "entite:edition");
        if ($this->getRoleUtilisateur()->hasRole($id_u, $role, $id_e)) {
            $this->setLastError("Ce droit a déjà été attribué à l'utilisateur");
        } elseif ($role) {
            $this->getRoleUtilisateur()->addRole($id_u, $role, $id_e);
        }
        $this->redirect("/Utilisateur/detail?id_u=$id_u");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function supprimeRoleAction()
    {
        $recuperateur = new Recuperateur($_POST);
        $id_u = $recuperateur->get('id_u');
        $role = $recuperateur->get('role');
        $id_e = $recuperateur->getInt('id_e', 0);
        $this->verifDroit($id_e, "entite:edition");
        $this->getRoleUtilisateur()->removeRole($id_u, $role, $id_e);
        $role_info = $this->getRoleSQL()->getInfo($role);
        $utilisateur_info = $this->getUtilisateur()->getInfo($id_u);

        $this->setLastMessage("Le rôle <i>{$role_info['libelle']}</i> a été retiré de l'utilisateur <i>{$utilisateur_info['prenom']} {$utilisateur_info['nom']}</i>");
        $this->redirect("/Utilisateur/detail?id_u=$id_u");
    }

    /**
     * @param $id_u
     * @param $id_e
     * @param $type
     * @param bool $page_moi
     * @return bool
     * @throws LastErrorException
     * @throws LastMessageException
     */
    private function verifEditNotification($id_u, $id_e, $type, $page_moi = false)
    {
        $utilisateur_info = $this->getUtilisateur()->getInfo($id_u);

        if (
            $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:edition", $id_e)
            &&
            $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "utilisateur:edition", $utilisateur_info['id_e'])
        ) {
            return true;
        }

        if (
            $id_u == $this->getId_u()
            &&
            $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:lecture", $id_e)
            &&
            $this->getDroitService()->hasDroit($this->getId_u(), $this->getDroitService()->getDroitLecture($type), $id_e)
        ) {
            return true;
        }

        $this->setLastError("Vous n'avez pas les droits nécessaires pour faire cela");
        $this->redirectToPageUtilisateur($id_u, $page_moi);
        return false;
    }

    /**
     * @param $id_u
     * @param $page_moi
     * @throws LastErrorException
     * @throws LastMessageException
     */
    private function redirectToPageUtilisateur($id_u, $page_moi = false)
    {
        if ($page_moi) {
            $this->redirect("/Utilisateur/moi");
        } else {
            $this->redirect("/Utilisateur/detail?id_u=$id_u");
        }
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function notificationAjoutAction()
    {

        $recuperateur = $this->getPostInfo();

        $id_u = $recuperateur->getInt('id_u');
        $id_e = $recuperateur->getInt('id_e', 0);
        $type = $recuperateur->get('type', 0);
        $daily_digest = $recuperateur->getInt('daily_digest', 0);
        $page_moi = $recuperateur->get('moi', false);

        $this->verifEditNotification($id_u, $id_e, $type, $page_moi);
        $this->getNotification()->add($id_u, $id_e, $type, 0, $daily_digest);
        $this->setLastMessage("La notification a été ajoutée");
        $this->redirectToPageUtilisateur($id_u, $page_moi);
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function notificationAction()
    {
        $recuperateur = $this->getGetInfo();

        $id_u = $recuperateur->getInt('id_u');
        $id_e = $recuperateur->getInt('id_e');
        $type = $recuperateur->get('type');
        $from_me = $recuperateur->get('from_me', false);
        $page_moi = $recuperateur->get('moi', false);
        $this->{'page_moi'} = $page_moi;

        if ($page_moi) {
            $this->{'pages_without_left_menu'} = true;
        }

        $utilisateur_info = $this->getUtilisateur()->getInfo($id_u);
        $this->verifEditNotification($id_u, $id_e, $type, $page_moi);

        $this->{'has_daily_digest'} = $this->getNotification()->hasDailyDigest($id_u, $id_e, $type);

        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
        $titreSelectAction = $type ? "Paramètre des notification des documents de type " . $type : "La sélection des actions n'est pas possible car aucun type de dossier n'est spécifié";

        $action_list = $documentType->getAction()->getActionWithNotificationPossible();

        $this->{'titreSelectAction'} = $titreSelectAction;
        $this->{'action_list'} = $this->getNotification()->getNotificationActionList($id_u, $id_e, $type, $action_list);
        $this->{'id_u'} = $id_u;
        $this->{'id_e'} = $id_e;
        $this->{'type'} = $type;

        if ($from_me) {
            $this->{'cancel_url'} = "/Utilisateur/moi";
        } else {
            $this->{'cancel_url'} = "/Utilisateur/detail?id_u=$id_u&id_e=$id_e";
        }

        $this->{'page_title'} = get_hecho($utilisateur_info['login']) . " - abonnement aux actions des documents ";
        $this->{'template_milieu'} = "UtilisateurNotification";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function notificationSuppressionAction()
    {
        $recuperateur = $this->getPostInfo();

        $id_n = $recuperateur->get('id_n');
        $page_moi = $recuperateur->get('moi', false);

        $infoNotification = $this->getNotification()->getInfo($id_n);
        $id_u = $infoNotification['id_u'];
        $id_e = $infoNotification['id_e'];
        $type = $infoNotification['type'];

        $this->verifEditNotification($id_u, $id_e, $type, $page_moi);
        $this->getNotification()->removeAll($id_u, $id_e, $type);
        $this->setLastMessage("La notification a été supprimée");
        $this->redirectToPageUtilisateur($id_u, $page_moi);
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doNotificationEditAction()
    {
        $recuperateur = $this->getPostInfo();
        $id_u = $recuperateur->getInt('id_u');
        $id_e = $recuperateur->getInt('id_e');
        $type = $recuperateur->get('type');
        $daily_digest = $recuperateur->get('has_daily_digest');
        $page_moi = $recuperateur->get('moi', false);

        $this->getUtilisateur()->getInfo($id_u);
        $this->verifEditNotification($id_u, $id_e, $type, $page_moi);

        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);

        $action_list = $documentType->getAction()->getActionWithNotificationPossible();

        $all_checked = true;
        $no_checked = false;
        $action_checked = array();
        foreach ($action_list as $action) {
            $checked = !!$recuperateur->get($action['id']);
            $action_checked[$action['id']] = $checked;
            $all_checked = $all_checked && $checked;
            $no_checked = $no_checked || $checked;
        }

        $this->getNotification()->removeAll($id_u, $id_e, $type);

        $this->setLastMessage("Les notifications ont été modifiées");
        if (!$no_checked) {
            $this->redirectToPageUtilisateur($id_u, $page_moi);
        }
        if ($all_checked) {
            $this->getNotification()->add($id_u, $id_e, $type, Notification::ALL_TYPE, $daily_digest);
            $this->redirectToPageUtilisateur($id_u, $page_moi);
        }
        foreach ($action_list as $action) {
            if (!$action_checked[$action['id']]) {
                continue;
            }
            $this->getNotification()->add($id_u, $id_e, $type, $action['id'], $daily_digest);
        }
        $this->redirectToPageUtilisateur($id_u, $page_moi);
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function notificationToogleDailyDigestAction()
    {
        $recuperateur = $this->getPostInfo();
        $id_n = $recuperateur->getInt('id_n');
        $page_moi = $recuperateur->get('moi', false);
        $infoNotification = $this->getNotification()->getInfo($id_n);
        $id_u = $infoNotification['id_u'];
        $id_e = $infoNotification['id_e'];
        $type = $infoNotification['type'];

        $this->verifEditNotification($id_u, $id_e, $type, $page_moi);
        $this->getNotification()->toogleDailyDigest($id_u, $id_e, $type);
        $this->setLastMessage("La notification a été modifié");
        $this->redirectToPageUtilisateur($id_u, $page_moi);
    }

    public function getCertificatAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $verif_number = $recuperateur->get('verif_number');

        $utilisateurListe = $this->getUtilisateurListe();

        $liste = $utilisateurListe->getUtilisateurByCertificat($verif_number, 0, 1);

        if (count($liste) < 1) {
            header("Location: index.php");
            exit;
        }


        $certificat = new Certificat($liste[0]['certificat']);


        header("Content-type: text/plain");
        header("Content-disposition: attachment; filename=" . $verif_number . ".pem");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");

        echo $certificat->getContent();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doModifPasswordAction()
    {
        $recuperateur = new Recuperateur($_POST);

        $oldpassword = $recuperateur->get('old_password');
        $password = $recuperateur->get('password');
        $password2 = $recuperateur->get('password2');
        if ($password != $password2) {
            $this->setLastError("Les mots de passe ne correspondent pas");
            $this->redirect("Utilisateur/modifPassword");
        }


        if (!$this->getUtilisateur()->verifPassword($this->getId_u(), $oldpassword)) {
            $this->setLastError("Votre ancien mot de passe est incorrecte");
            $this->redirect("Utilisateur/modifPassword");
        }

        $passwordEntropy = $this->getObjectInstancier()->getInstance(PasswordEntropy::class);
        if (! $passwordEntropy->isPasswordStrongEnough($password)) {
            $this->setLastError(
                "Le mot de passe n'a pas été changé car le nouveau mot de passe n'est pas assez fort.<br/>" .
                "Essayez de l'allonger ou de mettre des caractères de différents types. La barre de vérification doit être entièrement remplie"
            );
            $this->redirect("Utilisateur/modifPassword");
        }

        $this->getUtilisateur()->setPassword($this->getId_u(), $password);

        $this->setLastMessage("Votre mot de passe a été modifié");
        $this->redirect("/Utilisateur/moi");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function supprimerCertificatAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_u = $recuperateur->get('id_u');

        $info = $this->getUtilisateur()->getInfo($id_u);

        $this->verifDroit($info['id_e'], "utilisateur:edition");

        $this->getUtilisateur()->removeCertificat($id_u);

        $this->redirect("/Utilisateur/edition?id_u=$id_u");
    }
}
