<?php
class ConnecteurControler extends PastellControler {

	/**
	 * @return ConnecteurDefinitionFiles
	 */
	protected function getConnecteurDefinitionFile(){
		$this->getInstance('ConnecteurDefinitionFiles');
	}

	/**
	 * @return ConnecteurAPIController
	 */
	private function getConnecteurController(){
		return $this->getAPIController('Connecteur');
	}

	public function verifDroitOnConnecteur($id_ce){
		$connecteur_entite_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
		if (! $connecteur_entite_info) {
			$this->setLastError("Ce connecteur n'existe pas");
			$this->redirect("/Entite/detail?page=3");
		}
		$this->hasDroitEdition($connecteur_entite_info['id_e']);		
		return $connecteur_entite_info;
	}
	
	public function doNewAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		try {
        	if ($id_e) {
				$this->hasDroitEdition($id_e);
			}

			$this->getConnecteurController()->createAction();

			$this->setLastMessage("Connecteur ajouté avec succès");
			$this->redirect("/Entite/detail?id_e=$id_e&page=3");                    
		} catch (Exception $ex) {
			$this->setLastError($ex->getMessage());
		    $this->redirect("/Connecteur/new?id_e=$id_e");
		} 
	}

        
	public function doDeleteAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_ce = $recuperateur->getInt('id_ce');
		
		try {
			$info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
			$this->getConnecteurController()->deleteAction();
			$this->setLastMessage("Le connecteur « {$info['libelle']} » a été supprimé.");
			$this->redirect("/Entite/detail?id_e={$info['id_e']}&page=3");
		} catch (Exception $ex) {
			$this->setLastError($ex->getMessage());
			$this->redirect("/Connecteur/edition?id_ce=$id_ce");
		}                                
	}
        

	public function doEditionLibelleAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_ce = $recuperateur->getInt('id_ce');
		$libelle = $recuperateur->get('libelle');

		try {
			$this->getConnecteurController()->editAction();
		} catch (Exception $ex) {
			$this->getLastError()->setLastError($ex->getMessage());
			$this->redirect("/Connecteur/editionLibelle?id_ce=$id_ce");
		}
		$this->getLastMessage()->setLastMessage("Le connecteur « $libelle » a été modifié.");
		$this->redirect("/Connecteur/edition?id_ce=$id_ce");
	}

	public function doEditionModifAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_ce = $recuperateur->getInt('id_ce');
		$this->verifDroitOnConnecteur($id_ce);
		
		$fileUploader = new FileUploader();
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
		$donneesFormulaire->saveTab($recuperateur,$fileUploader,0);
		
		foreach($donneesFormulaire->getOnChangeAction() as $action) {	
			$this->getActionExecutorFactory()->executeOnConnecteur($id_ce,$this->getId_u(),$action);
		}
		
		$this->redirect("/connecteur/edition?id_ce=$id_ce");
	}

	public function recupFileAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$field = $recuperateur->get('field');
		$num = $recuperateur->getInt('num');
		
		$this->verifDroitOnConnecteur($id_ce);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
		$filePath = $donneesFormulaire->getFilePath($field,$num);
		if (!$filePath){
			$this->setLastError("Ce fichier n'existe pas");
			$this->redirect("/connecteur/edition?id_ce=$id_ce");
		}
		$fileName = $donneesFormulaire->getFileName($field,$num);
		
		header("Content-type: ".mime_content_type($filePath));
		header("Content-disposition: attachment; filename=\"$fileName\"");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
		readfile($filePath);
	}
	
	public function deleteFileAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$field = $recuperateur->get('field');
		$num = $recuperateur->getInt('num');
		
		$this->verifDroitOnConnecteur($id_ce);
		
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
		$donneesFormulaire->removeFile($field,$num);
		
		$this->redirect("/Connecteur/editionModif?id_ce=$id_ce");
	}

	public function deleteAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$this->verifDroitOnConnecteur($id_ce);		
		
		$this->{'connecteur_entite_info'} = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
		
		$this->{'page_title'} = "Suppression du connecteur  « {$this->{'connecteur_entite_info'}['libelle']} »";
		$this->{'template_milieu'} = "ConnecteurDelete";
		$this->renderDefault();
	}

	private function setConnecteurInfo(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$this->verifDroitOnConnecteur($id_ce);
		$connecteur_entite_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
		$id_e = $connecteur_entite_info['id_e'];
		$entite_info = $this->getEntiteSQL()->getInfo($id_e);


		try {
			$donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
			$this->{'donneesFormulaire'} = $donneesFormulaire;
		} catch (Exception $e){
			$this->setLastError("Impossible de trouver la défintion pour le connecteur de type {$connecteur_entite_info['type']} ");
			$this->redirect("Entite/detail?id_e=$id_e&page=3");
		}
		
		$this->{'inject'} = array('id_e'=>$id_e,'id_ce'=>$id_ce,'id_d'=>'','action'=>'');
		
		$this->{'my_role'} = "";
		

		
		if ($connecteur_entite_info['id_e']){
			$this->{'action'} = $this->getDocumentTypeFactory()->getEntiteDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
		} else {
			$this->{'action'} = $this->getDocumentTypeFactory()->getGlobalDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
		} 
		
		
		if (! $id_e){
			$entite_info['denomination'] = "Entité racine";
		}
		$this->{'entite_info'} = $entite_info;
		$this->{'connecteur_entite_info'} = $connecteur_entite_info;
		$this->{'id_ce'} = $id_ce;
		$this->{'id_e'} = $id_e;
	}

	public function editionModifAction(){
		$this->setConnecteurInfo();
		$this->{'page_title'} = "Configuration des connecteurs pour « {$this->{'entite_info'}['denomination']} »";
		$this->{'action_url'} = "Connecteur/doEditionModif";
		$this->{'recuperation_fichier_url'} = "Connecteur/recupFile?id_ce=".$this->{'id_ce'};
		$this->{'suppression_fichier_url'} = "Connecteur/deleteFile?id_ce=".$this->{'id_ce'};
		$this->{'page'} = 0;
		$this->{'externalDataURL'} = "Connecteur/externalData" ;
		$this->{'template_milieu'} = "ConnecteurEditionModif";
		$this->renderDefault();
	}
	
	public function editionAction(){
		$this->setConnecteurInfo();
		$this->{'page_title'} = "Configuration des connecteurs pour « {$this->{'entite_info'}['denomination']} »";
		$this->{'recuperation_fichier_url'} = "Connecteur/recupFile?id_ce=".$this->{'id_ce'};
		$this->{'template_milieu'} = "ConnecteurEdition";
		$this->{'fieldDataList'} = $this->{'donneesFormulaire'}->getFieldDataListAllOnglet($this->{'my_role'});
		$this->{'job_list'} = $this->getWorkerSQL()->getJobListWithWorkerForConnecteur($this->{'id_ce'});
		$this->{'return_url'} = urlencode("connecteur/edition?id_ce={$this->{'id_ce'}}");
		
		$this->renderDefault();
	}
	
	public function newAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$this->verifDroit($id_e, "entite:edition");
		
		$this->{'id_e'} = $id_e;
		$this->{'all_connecteur_dispo'} = $this->getConnecteurDefinitionFile()->getAllByIdE($id_e);
		
		$this->{'page_title'} = "Ajout d'un connecteur";
		$this->{'template_milieu'} = "ConnecteurNew";
		$this->renderDefault();
	}
	
	public function editionLibelleAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		
		$this->verifDroitOnConnecteur($id_ce);
		
		$this->{'connecteur_entite_info'} = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
		
		$this->{'page_title'} = "Modification du connecteur  « {$this->{'connecteur_entite_info'}['libelle']} »";
		$this->{'template_milieu'} = "ConnecteurEditionLibelle";
		$this->renderDefault();
	}


	public function exportAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$this->verifDroitOnConnecteur($id_ce);

		/** @var ConnecteurFactory $connecteurFactory */
		$connecteurFactory = $this->{'ConnecteurFactory'};

		$connecteurConfig = $connecteurFactory->getConnecteurConfig($id_ce);


		$connecteurEntite = $this->getConnecteurEntiteSQL();
		$info = $connecteurEntite->getInfo($id_ce);


		$filename = strtr($info['libelle']," ","_").".json";

		header("Content-type: application/json");
		header("Content-disposition: attachment; filename=\"$filename\"");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
		echo $connecteurConfig->jsonExport();
	}

	public function importAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');

		$this->verifDroitOnConnecteur($id_ce);

		$this->{'connecteur_entite_info'} = $this->getConnecteurEntiteSQL()->getInfo($id_ce);

		$this->{'page_title'} = "Importer des données pour le connecteur  « {$this->{'connecteur_entite_info'}['libelle']} »";
		$this->{'template_milieu'} = "ConnecteurImport";
		$this->renderDefault();
	}

	public function doImportAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_ce = $recuperateur->getInt('id_ce');

		$this->verifDroitOnConnecteur($id_ce);
		$fileUploader = new FileUploader();
		$file_content = $fileUploader->getFileContent('pser');

		/** @var ConnecteurFactory $connecteurFactory */
		$connecteurFactory = $this->{'ConnecteurFactory'};

		$connecteurConfig = $connecteurFactory->getConnecteurConfig($id_ce);
		try {
			$connecteurConfig->jsonImport($file_content);
			$this->setLastMessage("Les données du connecteur ont été importées");
		} catch (Exception $e){
			$this->setLastError($e->getMessage());
		}

		$this->redirect("/connecteur/edition?id_ce=$id_ce");
	}
	
	public function actionAction(){

		$recuperateur = new Recuperateur($_POST);

		$action = $recuperateur->get('action');
		$id_ce = $recuperateur->getInt('id_ce',0);

		$actionPossible = $this->getActionPossible();

		if ( ! $actionPossible->isActionPossibleOnConnecteur($id_ce,$this->getId_u(),$action)) {
			$this->setLastError("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule() );
			$this->redirect("/Connecteur/edition?id_ce=$id_ce");
		}

		$result = $this->getActionExecutorFactory()->executeOnConnecteur($id_ce,$this->getId_u(),$action);

		$message = $this->getActionExecutorFactory()->getLastMessage();

		if (! $result ){
			$this->setLastError($message);
		} else {
			$this->setLastMessage($message);
		}

		$this->redirect("/Connecteur/edition?id_ce=$id_ce");
	}

	public function externalDataAction(){

		$recuperateur = new Recuperateur($_GET);
		$field = $recuperateur->get('field');
		$id_ce = $recuperateur->get('id_ce');

		$connecteur_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
		$id_e  = $connecteur_info['id_e'];

		if ( ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),"entite:edition",$id_e)) {
			$this->setLastError("Vous n'avez pas le droit de faire cette action (entite:edition)");
			$this->redirect("/Connecteur/editionModif?id_ce=$id_ce");
		}

		/** @var DocumentType $documentType */
		$documentType = $this->getDocumentTypeFactory()->getEntiteDocumentType($connecteur_info['id_connecteur']);
		$formulaire = $documentType->getFormulaire();

		$action_name =  $formulaire->getField($field)->getProperties('choice-action');
		$result = $this->getActionExecutorFactory()->displayChoiceOnConnecteur($id_ce,$this->getId_u(),$action_name,$field);
		if (! $result){
			$this->setLastError($this->getActionExecutorFactory()->getLastMessage());
			$this->redirect("Location: /Connecteur/editionModif?id_ce=$id_ce");

		}
	}

	public function doExternalDataAction(){
		$recuperateur = new Recuperateur($_REQUEST);
		$id_ce = $recuperateur->get('id_ce');
		$field = $recuperateur->get('field');

		$connecteur_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
		$id_e  = $connecteur_info['id_e'];

		if ( ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),"entite:edition",$id_e)) {
			$this->setLastError("Vous n'avez pas le droit de faire cette action (entite:edition)");
			$this->redirect("/Connecteur/edition?id_ce=$id_ce");
			exit;
		}

		/** @var DocumentType $documentType */
		$documentType = $this->getDocumentTypeFactory()->getEntiteDocumentType($connecteur_info['id_connecteur']);
		$formulaire = $documentType->getFormulaire();
		$theField = $formulaire->getField($field);

		$action_name = $theField->getProperties('choice-action');
		$this->getActionExecutorFactory()->goChoiceOnConnecteur($id_ce,$this->getId_u(),$action_name,$field);
	}


}