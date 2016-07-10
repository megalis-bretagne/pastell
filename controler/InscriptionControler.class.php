<?php 

class InscriptionControler extends PastellControler {
	
	public function citoyenIndexAction(){
		$this->{'page_title'} = "Inscription sur Pastell";
		$this->{'template_milieu'} = "InscriptionCitoyenIndex";
		$this->renderDefault();
	}
	
	public function citoyenOKAction(){
		$this->{'page_title'} = "Inscription en cours";
		$this->{'template_milieu'} = "InscriptionCitoyenOK";
		$this->renderDefault();
	}
	
	public function fournisseurIndexAction(){
		$this->{'page_title'} = "Inscription sur Pastell";
		$this->{'template_milieu'} = "InscriptionFournisseurIndex";
		$this->renderDefault();
	}
	
	public function fournisseurOKAction(){
		$this->{'page_title'} = "Inscription en cours";
		$this->{'template_milieu'} = "InscriptionFournisseurOK";
		$this->renderDefault();
	}
	
	public function fournisseurMailAction(){
		if (! $this->getId_u()){
			$this->redirect("/Connexion/connexion");
		}
		$this->{'infoUtilisateur'} = $this->getUtilisateur()->getInfo($this->getId_u());
		$this->{'page_title'} = "Inscription en cours de finalisation";
		$this->{'template_milieu'} = "InscriptionFournisseurMail";
		$this->renderDefault();
	}

	public function doInscriptionCitoyenAction(){


		$recuperateur = new Recuperateur($_POST);
		$email = $recuperateur->get('email');
		$password = $recuperateur->get('password');
		$password2 = $recuperateur->get('password2');


		if ( ! $email ){
			$this->setLastError("Il faut saisir un email");
			$this->redirect("/Inscription/citoyenIndex");
		}

		$entite = new Entite($this->getSQLQuery(),$email);
		if ($entite->exists()){
			$this->setLastError("L'adresse que vous avez déjà indiqué est déjà connu sur la plateforme");
			$this->redirect("/Inscription/citoyenIndex");
		}
		

		$id_u = $this->getUtilisateurCreator()->create($email,$password,$password2,$email);

		if ( ! $id_u){
			$this->setLastError($this->getUtilisateurCreator()->getLastError());
			$this->redirect("/Inscription/citoyenIndex");
		}
		

		$entiteCreator = new EntiteCreator($this->getSQLQuery(),$this->getJournal());
		$id_e = $entiteCreator->edit(false,0,$email,Entite::TYPE_CITOYEN,0,0);

		$this->getRoleUtilisateur()->addRole($id_u,"citoyen",$id_e);


		$this->getUtilisateur()->validMailAuto($id_u);

		$this->redirect("/Inscription/citoyenOK");
	}

	public function desinscriptionFournisseurAction(){
		//FIXME (ou pas) : ca a sauté à un moment donné : les classes n'existent plus
		throw new Exception("Not implemented");

		/*$utilisateurEntite = new UtilisateurEntite($sqlQuery,$authentification->getId());

		$entite = new Entite($sqlQuery, $utilisateurEntite->getSiren());
		$result = $entite->desinscription();

		if($result){
			$utilisateur = new Utilisateur($sqlQuery);
			$utilisateur->desinscription($authentification->getId());

		}
		header("Location: index.php");*/
	}

	public function doInscriptionFournisseurAction(){


		$recuperateur = new Recuperateur($_POST);
		$email = $recuperateur->get('email');
		$siren = $recuperateur->get('siren');
		$login = $recuperateur->get('login');
		$password = $recuperateur->get('password');
		$password2 = $recuperateur->get('password2');
		$nom = $recuperateur->get('nom');
		$prenom = $recuperateur->get('prenom');
		$denomination = $recuperateur->get('denomination');


		$entite = new Entite($this->getSQLQuery(),$siren);
		if ($entite->exists()){
			$this->setLastError("Le siren que vous avez déjà indiqué est déjà connu sur la plateforme");
			$this->redirect();
		}

		$sirenVerifier = new Siren();
		if (! $sirenVerifier->isValid($siren)){
			$this->setLastError("Votre siren ne semble pas valide");
			$this->redirect();
		}

		if ( ! $denomination ){
			$this->setLastError("Il faut saisir une raison sociale");
			$this->redirect();
		}

		$id_u = $this->getUtilisateurCreator()->create($login,$password,$password2,$email);

		if ( ! $id_u){
			$this->setLastError($this->getUtilisateurCreator()->getLastError());
			$this->redirect();
		}

		$utilisateur = new Utilisateur($this->getSQLQuery());
		$utilisateur->setNomPrenom($id_u,$nom,$prenom);

		$entiteCreator = new EntiteCreator($this->getSQLQuery(),$this->getJournal());
		$id_e = $entiteCreator->edit(false,$siren,$denomination,Entite::TYPE_FOURNISSEUR,0,0);

		$this->getRoleUtilisateur()->addRole($id_u,"fournisseur",$id_e);

		$infoUtilisateur = $utilisateur->getInfo($id_u);

		$zMail = $this->getZenMail();
		$mailVerification = new MailVerification($zMail);
		$mailVerification->send($infoUtilisateur);

		$this->redirect("Inscription/fournisseurOK");

	}

	public function mailVerificationFournisseurAction(){
		$recuperateur = new Recuperateur($_GET);

		$password = $recuperateur->get('password');
		$login = $recuperateur->get('login');

		$utilisateurListe = new UtilisateurListe($this->getSQLQuery());
		$id_u = $utilisateurListe->getUtilisateurByLogin($login);

		$utilisateur = new Utilisateur($this->getSQLQuery());
		$result = $utilisateur->validMail($id_u,$password);

		if ($result){
			$this->setLastMessage("Votre mail est maintenant validé");
		} else {
			$this->setLastError("Le mail n'a pas pu être validé");
		}

		$this->redirect("/Connexion/connexion");
	}

	public function renvoieMailFournisseurAction(){

		$utilisateur = new Utilisateur($this->getSQLQuery());
		$infoUtilisateur = $utilisateur->getInfo($this->getId_u());

		if ( ! $infoUtilisateur || $infoUtilisateur['mail_verifie']){
			header("Location: " . SITE_BASE ."index.php");
			exit;
		}

		$zMail = $this->getZenMail();
		$mailVerification = new MailVerification($zMail);
		$mailVerification->send($infoUtilisateur);

		$this->redirect("/Inscription/fournisseurOK");
	}


}