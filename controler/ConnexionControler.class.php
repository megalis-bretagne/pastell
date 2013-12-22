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
			$this->redirect("/connexion/connexion.php");
		}
	}
	
	public function casAuthentication(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$casAuthentication = $this->ConnecteurFactory->getConnecteurById($id_ce);
		$login = $casAuthentication->authenticate(SITE_BASE."/connexion/cas-pastell.php?id_ce=$id_ce");
		$this->LastMessage->setLastMessage("Authentification avec le login : $login");
		$this->redirect("/connecteur/edition.php?id_ce=$id_ce");
	}
	
	public function apiCasConnexion(){
		$authentificationConnecteur = $this->ConnecteurFactory->getGlobalConnecteur("authentification");
		
		if ( ! $authentificationConnecteur){
			return false;
		}
	
		$login = $authentificationConnecteur->authenticate();
		if (!$login){
			throw new Exception("Le serveur CAS n'a pas donn� de login");
		}
		$id_u = $this->UtilisateurListe->getUtilisateurByLogin($login);
		if (!$id_u){
			throw new Exception("Votre login cas est inconnu sur Pastell ($login) ");
		}
		
		$verificationConnecteur = $this->ConnecteurFactory->getGlobalConnecteur("V�rification");
		
		if (! $verificationConnecteur){
			return $id_u;
		}
		
		if (! $verificationConnecteur->getEntry($login)){
			throw new Exception("Vous ne pouvez pas vous connecter car vous �tes inconnu sur l'annuaire LDAP");
		}
		return $id_u;
	}
	
	private function setConnexion($id_u){
		$infoUtilisateur = $this->Utilisateur->getInfo($id_u);
		$login = $infoUtilisateur['login'];
		$this->Journal->setId($id_u);
		$nom = $infoUtilisateur['prenom']." ".$infoUtilisateur['nom'];
		$this->Journal->add(Journal::CONNEXION,$infoUtilisateur['id_e'],0,"Connect�","$nom s'est connect� via CAS depuis l'adresse ".$_SERVER['REMOTE_ADDR']);
		
		$this->Authentification->connexion($login, $id_u);
		
	}
	
	private function casConnexion(){		
		try{
			$id_u = $this->apiCasConnexion();
			if (! $id_u) {
				return false;
			}
			$this->setConnexion($id_u);
		} catch(Exception $e){
			$this->LastError->setLastError($e->getMessage());
			$this->redirect("/connexion/cas-error.php");
		}
		return $id_u;		
	}
	
	public function connexionAdminAction() {
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
		$this->page_title = "Oublie des identifiants";
		$this->template_milieu = "ConnexionOublieIdentifiant";
		$this->renderDefault();
	}
	
	public function changementMdpAction(){
		$recuperateur = new Recuperateur($_GET);
		$this->mail_verif_password = $recuperateur->get('mail_verif');
		
		$this->page="oublie_identifiant";
		$this->page_title="Oublie des identifiants";
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
			
		$authentificationConnecteur = $this->ConnecteurFactory->getGlobalConnecteur("authentification");
		
		if ($authentificationConnecteur){
			$authentificationConnecteur->logout();
		}
		$this->redirect("/connexion/connexion.php");
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
		$id_u = $this->utilisateurListe->getUtilisateurByLogin($login);
		
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
		$this->Journal->add(Journal::CONNEXION,$infoUtilisateur['id_e'],0,"Connect�","$nom s'est connect� depuis l'adresse ".$_SERVER['REMOTE_ADDR']);
		$this->Authentification->connexion($login, $id_u);
		return $id_u;
	}
	
	public function doConnexionAction(){		
		$this->connexionActionRedirect("connexion/connexion.php");
		$this->redirect();
	}
	
}