<?php 
class FournisseurControler extends PastellControler {

	/**
	 * @return UtilisateurCreator
	 */
	private function getUtilisateurCreator(){
		return $this->getInstance("UtilisateurCreator");
	}

	/** @return EntiteCreator */
	private function getEntiteCreator(){
		return $this->getInstance('EntiteCreator');
	}

	/** @return CollectiviteFournisseurSQL */
	private function getCollectiviteFournisseurSQL(){
		return $this->getInstance('CollectiviteFournisseurSQL');
	}


	/**
	 * //FIXME OMG : Pastell dépend du flux fournisseur...
	 * @param int $id_e
	 * @return MailFournisseurInvitation
	 */
	public function getMailFournisseurInvitation($id_e){
		return $this->getConnecteurFactory()->getConnecteurByType($id_e,"fournisseur-invitation" ,"mail-fournisseur-invitation");
	}
	
	private function testFournisseurInvitation($id_e,$id_d,$secret){
		$mailFournisseurInvitation = $this->getMailFournisseurInvitation($id_e);

		if (! $mailFournisseurInvitation) {
			$this->setLastError("Un problème sur la collectivité empêche de terminer votre inscription");
			return false;
		} 
		if(! $mailFournisseurInvitation->verifSecret($this->getDonneesFormulaireFactory()->get($id_d),$secret)){
			$this->setLastError("Un problème de validation empêche de terminer votre inscription");
			return false;
		}

		/** @var DocumentActionSQL $documentActionSQL */
		$documentActionSQL = $this->getInstance("DocumentActionSQL");

		$documentActionInfo = $documentActionSQL->getLastActionInfo($id_d,$id_e);
		if ($documentActionInfo['action'] == 'fournisseur-inscrit'){
			$this->setLastError("Vous êtes déjà inscrit sur la plateforme à partir de cet email");
			return false;	
		}
		return true;
	}
	
	public function preInscriptionAction(){
		$recuperateur = new Recuperateur($_GET);
		$this->{'id_e'}= $recuperateur->getInt('id_e');
		$this->{'id_d'}= $recuperateur->get('id_d');
		$this->{'secret'}= $recuperateur->get('s');
		
		
		if ($this->testFournisseurInvitation($this->{'id_e'}, $this->{'id_d'}, $this->{'secret'})){
			$this->{'has_error'}= false;
			$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->{'id_d'});
			$this->{'email'}= $donneesFormulaire->get('email');
			$this->{'raison_sociale'}= $donneesFormulaire->get('raison_sociale');
		} else {
			$this->{'has_error'}= true;
		}
		
		$this->{'page_title'}= "Bienvenue sur Pastell";
		$this->{'template_milieu'}= "FournisseurPreInscription";
		$this->renderDefault();
	}
	
	private function redirectWithError($url_redirect,$error_message){
		$this->setLastError($error_message);
		$this->redirect($url_redirect);
	} 
	
	public function doInscriptionAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$id_d = $recuperateur->get('id_d');
		$secret = $recuperateur->get('s');
		$url_redirect = "Fournisseur/preInscription?id_e=$id_e&id_d=$id_d&s=$secret";
		if (! $this->testFournisseurInvitation($id_e, $id_d, $secret)){
			$this->redirect($url_redirect);
		}
		$email = $recuperateur->get('email');
		$siren = $recuperateur->get('siren');
		$login = $recuperateur->get('login');
		$password = $recuperateur->get('password');
		$password2 = $recuperateur->get('password2');
		$nom = $recuperateur->get('nom');
		$prenom = $recuperateur->get('prenom');
		$denomination = $recuperateur->get('denomination');
		
		if (!$login) {
			$this->redirectWithError($url_redirect,"L'identifiant est obligatoire");
		}
		if ($this->getEntiteSQL()->getBySiren($siren)){
			$this->redirectWithError($url_redirect,"Le siren que vous avez déjà indiqué est déjà connu sur la plateforme");
		}

		/** @var Siren $sirenVerifier */
		$sirenVerifier = new Siren();
		if (! $sirenVerifier->isValid($siren)){
			$this->redirectWithError($url_redirect,"Votre siren ne semble pas valide");
		}
		if ( ! $denomination ){
			$this->redirectWithError($url_redirect,"Il faut saisir une raison sociale");
		}

		$id_u = $this->getUtilisateurCreator()->create($login,$password,$password2,$email);
		if ( ! $id_u){
			$this->redirectWithError($url_redirect,$this->getUtilisateurCreator()->getLastError());
		}
		$this->getUtilisateur()->setNomPrenom($id_u,$nom,$prenom);
		$this->getUtilisateur()->validMailAuto($id_u);
		$new_id_e = $this->getEntiteCreator()->edit(false,$siren,$denomination,Entite::TYPE_FOURNISSEUR,0,0);
		$this->getRoleUtilisateur()->addRole($id_u,"fournisseur",$new_id_e);
		
		$this->getActionChange()->addAction($id_d,$new_id_e,$id_u,'fournisseur-inscrit',"Le fournisseur s'est inscrit avec le siren $siren et la raison sociale $denomination");
		$this->getCollectiviteFournisseurSQL()->add($id_e,$new_id_e);

		/** @var DocumentAPIController $documentAPIController */
		$documentAPIController = $this->getAPIController("Document");
		$documentAPIController->setRequestInfo(array('id_e'=>$new_id_e,'type'=>'fournisseur-inscription'));
		$result = $documentAPIController->createAction();
		$new_id_d = $result['id_d'];

		$this->getDocument()->setTitre($new_id_d,$denomination);

		$fournisseurInscription = $this->getDonneesFormulaireFactory()->get($new_id_d);
		$fournisseurInscription->setData('siren',$siren);
		$fournisseurInscription->setData('raison_sociale',$denomination);
		
		
		$this->setLastMessage("Votre inscription est terminée, vous pouvez vous connecter");
		$this->redirect("Connexion/connexion");
	}
	
	public function dejaInscritAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$id_d = $recuperateur->get('id_d');
		$secret = $recuperateur->get('s');
		$url_redirect = "Fournisseur/preInscription?id_e=$id_e&id_d=$id_d&s=$secret";
		if (! $this->testFournisseurInvitation($id_e, $id_d, $secret)){
			$this->redirect($url_redirect);
		}

		/** @var ConnexionControler $connexionControler */
		$connexionControler = $this->getInstance("ConnexionControler");

		$id_u = $connexionControler->connexionActionRedirect("Fournisseur/preInscription?id_e=$id_e&id_d=$id_d&s=$secret");
		
		$liste_entite = $this->getRoleUtilisateur()->getEntite($id_u,'fournisseur-inscription:edition');
		if (count($liste_entite) != 1){
			$this->redirectWithError($url_redirect,"Un problème a empêché l'inscription de cette collectivité.");
		}
		$id_e_fournisseur = $liste_entite[0];
		$this->getCollectiviteFournisseurSQL()->add($id_e,$id_e_fournisseur);
		//$this->ActionChange->addAction($id_d,$id_e_fournisseur,$id_u,'fournisseur-inscrit',"Le fournisseur s'est inscrit avec le siren $siren et la raison sociale $denomination");
		
		$entiteInfo = $this->getEntiteSQL()->getInfo($id_e);
		$this->setLastMessage("La collectivité {$entiteInfo['denomination']} a été ajouté à la liste. Veuillez soumettre vos informations (formulaire d'adhésion) à la collectivité.");
		$this->redirect("Document/index");
	}
	
	
}