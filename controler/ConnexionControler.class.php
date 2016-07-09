<?php
class ConnexionControler extends PastellControler {
	
	public function verifConnected(){
		if ($this->Authentification->isConnected()){
			return true;
		}
		try {
			$id_u = $this->apiCasConnexion();
			if ($id_u){
				$this->setConnexion($id_u);
			}
		} catch (Exception $e){}
		
		if (! $this->Authentification->isConnected()){
			$this->redirect("/Connexion/connexion");
		}

		return false;
	}
	
	public function casAuthenticationAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		/** @var CASAuthentication $casAuthentication */
		$casAuthentication = $this->ConnecteurFactory->getConnecteurById($id_ce);
		$login = $casAuthentication->authenticate(SITE_BASE."/Connexion/casAuthentication?id_ce=$id_ce");
		$this->LastMessage->setLastMessage("Authentification avec le login : $login");
		$this->redirect("/connecteur/edition?id_ce=$id_ce");
	}
	
	public function openIdReturn(){
		$recuperateur = new Recuperateur($_GET);
		$state = $recuperateur->get('state');
		$state = urldecode($state);
		$state_array = array();
		parse_str($state, $state_array);
		$id_ce = $state_array['id_ce'];
		/** @var OpenIDAuthentication $openIdAuthentication */
		$openIdAuthentication = $this->ConnecteurFactory->getConnecteurById($id_ce);
		if (!$openIdAuthentication){
			$this->redirect();
		}
		$sub = false;
		try {
			$sub = $openIdAuthentication->returnAuthenticate($recuperateur);
		} catch (Exception $e){
			$this->LastError->setLastError($e->getMessage());
			$this->redirect("Connexion/connexion");
		}
		
		$id_u = $this->Utilisateur->getIdFromLogin($sub);
		if (!$id_u){
			$this->LastError->setLastError("Aucun utilisateur ne correspond au login $sub");
			$this->redirect("Connexion/connexion");
		}
		
		$_SESSION['open_id_authenticate_id_ce'] = $id_ce;
		
		$this->setConnexion($id_u,"OpenID");
		$this->redirect();
	}
	
	public function apiCasConnexion(){
		/** @var CASAuthentication $authentificationConnecteur */
		$authentificationConnecteur = $this->ConnecteurFactory->getGlobalConnecteur("authentification");
		
		if ( ! $authentificationConnecteur){
			return false;
		}

		$login = $authentificationConnecteur->authenticate();
		if (!$login){
			throw new Exception("Le serveur CAS n'a pas donné de login");
		}
		$id_u = $this->UtilisateurListe->getUtilisateurByLogin($login);
		if (!$id_u){
			throw new Exception("Votre login cas est inconnu sur Pastell ($login) ");
		}

		/** @var LDAPVerification $verificationConnecteur */
		$verificationConnecteur = $this->ConnecteurFactory->getGlobalConnecteur("Vérification");
		
		if (! $verificationConnecteur){
			return $id_u;
		}
		
		if (! $verificationConnecteur->getEntry($login)){
			throw new Exception("Vous ne pouvez pas vous connecter car vous êtes inconnu sur l'annuaire LDAP");
		}
		return $id_u;
	}
	

	private function setConnexion($id_u,$external_system = "CAS"){
		$infoUtilisateur = $this->Utilisateur->getInfo($id_u);
		$login = $infoUtilisateur['login'];
		$this->Journal->setId($id_u);
		$nom = $infoUtilisateur['prenom']." ".$infoUtilisateur['nom'];
		$this->Journal->add(Journal::CONNEXION,$infoUtilisateur['id_e'],0,"Connecté","$nom s'est connecté via $external_system depuis l'adresse ".$_SERVER['REMOTE_ADDR']);		
		$this->Authentification->connexion($login, $id_u);
	}
	
	private function casConnexion(){
		$id_u = false;
		try{
			$id_u = $this->apiCasConnexion();
			if (! $id_u) {
				return false;
			}
			$this->setConnexion($id_u);
		} catch(Exception $e){
			$this->LastError->setLastError($e->getMessage());
			$this->redirect("/Connexion/casError");
		}
		return $id_u;		
	}
	
	public function adminAction() {
		$this->message_connexion = false;
		$this->page="connexion";
		$this->page_title="Connexion";
		$this->template_milieu = "ConnexionIndex";
		$this->renderDefault();
	}
	
	public function connexionAction(){
		if ($this->casConnexion()){
			$this->redirect();
		}

		/** @var MessageConnexion $messageConnexion */
		$messageConnexion = $this->ConnecteurFactory->getGlobalConnecteur("message-connexion");
		
		if ($messageConnexion){
			$this->message_connexion = $messageConnexion->getMessage();
		} else {
			$this->message_connexion = false;
		}
		
		$this->page="connexion";
		$this->page_title="Connexion";
		$this->template_milieu = "ConnexionIndex";
		$this->renderDefault();
	}
	
	public function oublieIdentifiantAction(){
		
		$config = false;
		try {
			$config = $this->ConnecteurFactory->getGlobalConnecteurConfig('message-oublie-identifiant');
		} catch(Exception $e){}
		
	
		$this->config = $config;
		
		$this->page="oublie_identifiant";
		$this->page_title = "Oubli des identifiants";
		$this->template_milieu = "ConnexionOublieIdentifiant";
		$this->renderDefault();
	}
	
	public function changementMdpAction(){
		$recuperateur = new Recuperateur($_GET);
		$this->mail_verif_password = $recuperateur->get('mail_verif');
		
		$this->page="oublie_identifiant";
		$this->page_title="Oubli des identifiants";
		$this->template_milieu = "ConnexionChangementMdp";
		$this->renderDefault();
	}
	
	public function noDroitAction(){
		$this->page_title="Pas de droit";
		$this->template_milieu = "ConnexionNoDroit";
		$this->renderDefault();
	}

	public function casErrorAction(){
		$this->page_title = "Erreur lors de l'authentification";
		$this->template_milieu = "CasError";
		$this->renderDefault();
	}
	
	public function logoutAction(){
		$this->Authentification->deconnexion();
		
		/** @var CSRFToken $csrfToken */
		$csrfToken = $this->getObjectInstancier()->getInstance('CSRFToken');
		$csrfToken->deleteToken();

		/** @var CASAuthentication $authentificationConnecteur */
		$authentificationConnecteur = $this->ConnecteurFactory->getGlobalConnecteur("authentification");
		if ($authentificationConnecteur){
			$authentificationConnecteur->logout();
		}
		
		if (isset($_SESSION['open_id_authenticate_id_ce'] )){
			/** @var OpenIDAuthentication $openIdAuthentication */
			$openIdAuthentication = $this->ConnecteurFactory->getConnecteurById($_SESSION['open_id_authenticate_id_ce']);
			if ($openIdAuthentication){
				$openIdAuthentication->logout();
			}
		}
		
		$this->redirect("/Connexion/connexion");
	}
	
	public function connexionActionRedirect($redirect_fail){
		$recuperateur = new Recuperateur($_POST);
		$login = $recuperateur->get('login');
		$password = $recuperateur->get('password');
		
		$authentificationConnecteur = $this->ConnecteurFactory->getGlobalConnecteur("authentification");
		
		if ($authentificationConnecteur && $login != 'admin'){
			$this->LastError->setLastError("Veuillez utiliser le serveur CAS pour l'authentification");
			$this->redirect($redirect_fail);
		}
		$id_u = $this->UtilisateurListe->getUtilisateurByLogin($login);
		
		if ( ! $this->Utilisateur->verifPassword($id_u,$password) ){
			$this->LastError->setLastError("Login ou mot de passe incorrect.");
			$this->redirect($redirect_fail);
		}
		
		if (! $this->CertificatConnexion->connexionGranted($id_u)){
			$this->LastError->setLastError("Vous devez avoir un certificat valide pour ce compte");
			$this->redirect($redirect_fail);
		}
		
		$this->Journal->setId($id_u);
		$infoUtilisateur = $this->Utilisateur->getInfo($id_u);
		$nom = $infoUtilisateur['prenom']." ".$infoUtilisateur['nom'];
		$this->Journal->add(Journal::CONNEXION,$infoUtilisateur['id_e'],0,"Connecté","$nom s'est connecté depuis l'adresse ".$_SERVER['REMOTE_ADDR']);
		$this->Authentification->connexion($login, $id_u);
		return $id_u;
	}
	
	public function doConnexionAction(){		
		$this->connexionActionRedirect("Connexion/connexion");
		$this->redirect();
	}
	
	public function renderOasisError(){
		$this->page="connexion";
		$this->page_title="Erreur";
		$this->template_milieu = "ConnexionOasisError";
		$this->renderDefault();
	}

	public function autoConnectAction(){
		$certificatConnexion = new CertificatConnexion($this->getSQLQuery());
		$id_u = $certificatConnexion->autoConnect();

		if ( ! $id_u ) {
			$this->redirect("/Connexion/index");
		}

		$utilisateur = new Utilisateur($this->getSQLQuery());
		$utilisateurInfo = $utilisateur->getInfo($id_u);

		$this->getJournal()->setId($id_u);
		$nom = $utilisateurInfo['prenom']." ".$utilisateurInfo['nom'];
		$this->getJournal()->add(Journal::CONNEXION,$utilisateurInfo['id_e'],0,"Connecté","$nom s'est connecté automatiquement depuis l'adresse ".$_SERVER['REMOTE_ADDR']);


		$this->Authentification->connexion($utilisateurInfo['login'],$id_u);
		$this->redirect();
	}

	public function doModifPasswordAction(){
		$recuperateur = new Recuperateur($_POST);

		$mail_verif_password = $recuperateur->get('mail_verif_password');
		$password = $recuperateur->get('password');
		$password2 = $recuperateur->get('password2');

		$utilisateurListe = new UtilisateurListe($this->getSQLQuery());
		$id_u = $utilisateurListe->getByVerifPassword($mail_verif_password);

		if ( ! $id_u ){
			$this->LastError->setLastError("Utilisateur inconnu");
			$this->redirect("/Connexion/index");
		}

		if (! $password){
			$this->LastError->setLastError("Le mot de passe est obligatoire");
			$this->redirect("/Connexion/changementMdp?mail_verif=$mail_verif_password");
		}
		if ($password != $password2){
			$this->LastError->setLastError("Les mots de passe ne correspondent pas");
			$this->redirect("/Connexion/index");
			$this->redirect("/Connexion/changementMdp?mail_verif=$mail_verif_password");
			exit;
		}


		$utilisateur = new Utilisateur($this->getSQLQuery());
		$infoUtilisateur = $utilisateur->getInfo($id_u);
		$utilisateur->setPassword($id_u,$password);

		$passwordGenerator = new PasswordGenerator();
		$mailVerifPassword = $passwordGenerator->getPassword();
		$utilisateur->reinitPassword($id_u,$mailVerifPassword);

		$this->getJournal()->add(Journal::MODIFICATION_UTILISATEUR,$infoUtilisateur['id_e'],0,"mot de passe modifié","{$infoUtilisateur['login']} ({$infoUtilisateur['id_u']}) a modifié son mot de passe");
		$this->LastMessage->setLastMessage("Votre mot de passe a été modifié");

		$this->redirect("/Connexion/index");
	}

	public function doOublieIdentifiantAction(){
		$recuperateur = new Recuperateur($_POST);

		$login = $recuperateur->get('login');
		$email = $recuperateur->get('email');


		$utilisateurListe = new UtilisateurListe($this->getSQLQuery());
		$id_u = $utilisateurListe->getByLoginOrEmail($login,$email);

		if (!$id_u){
			$this->LastError->setLastError("Aucun compte n'a été trouvé avec ces informations");
			$this->redirect("/Connexion/oublieIdentifiant");
		}
		$passwordGenerator = new PasswordGenerator();
		$mailVerifPassword = $passwordGenerator->getPassword();

		$utilisateur = new Utilisateur($this->getSQLQuery());
		$info = $utilisateur->getInfo($id_u);
		$utilisateur->reinitPassword($id_u,$mailVerifPassword);


		/** @var ZenMail $zenMail */
		$zenMail = $this->getObjectInstancier()->getInstance("ZenMail");
		$zenMail->setEmetteur("Pastell",PLATEFORME_MAIL);
		$zenMail->setDestinataire($info['email']);
		$zenMail->setSujet("[Pastell] Procédure de modification de mot de passe");
		$infoMessage = array('mail_verif_password'=>$mailVerifPassword);
		$zenMail->setContenu(PASTELL_PATH . "/mail/changement-mdp.php",$infoMessage);
		$zenMail->send();

		$this->getJournal()->addActionAutomatique(Journal::MODIFICATION_UTILISATEUR,$info['id_e'],0,'mot de passe modifié',"Procédure initiée pour {$info['email']}");

		$this->LastMessage->setLastMessage("Un email vous a été envoyé avec la suite de la procédure");
		$this->redirect("/Connexion/index");
	}

	public function indexAction(){
		return $this->connexionAction();
	}

}