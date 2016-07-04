<?php
class ConnecteurControler extends PastellControler {

	/**
	 * @return ConnecteurDefinitionFiles
	 */
	protected function getConnecteurDefinitionFile(){
		return $this->ConnecteurDefinitionFiles;
	}

	/**
	 * @return ConnecteurAPIController
	 */
	private function getConnecteurController(){
		return $this->getAPIController('Connecteur');
	}

	public function verifDroitOnConnecteur($id_ce){
		$connecteur_entite_info = $this->ConnecteurEntiteSQL->getInfo($id_ce);
		if (! $connecteur_entite_info) {
			$this->LastError->setLastError("Ce connecteur n'existe pas");
			$this->redirect("/entite/detail.php?page=3");
		}
		$this->hasDroitEdition($connecteur_entite_info['id_e']);		
		return $connecteur_entite_info;
	}
	
	public function doNouveau(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		try {
        	if ($id_e) {
				$this->hasDroitEdition($id_e);
			}

			$this->getConnecteurController()->createAction();

			$this->LastMessage->setLastMessage("Connecteur ajouté avec succès");
			$this->redirect("/entite/detail.php?id_e=$id_e&page=3");                    
		} catch (Exception $ex) {
			$this->LastError->setLastError($ex->getMessage());
		    $this->redirect("/connecteur/new.php?id_e=$id_e");
		} 
	}

        
	public function doDelete(){
		$recuperateur = new Recuperateur($_POST);
		$id_ce = $recuperateur->getInt('id_ce');
		
		try {
			$info = $this->ConnecteurEntiteSQL->getInfo($id_ce);
			$this->getConnecteurController()->deleteAction();
			$this->LastMessage->setLastMessage("Le connecteur « {$info['libelle']} » a été supprimé.");
			$this->redirect("/entite/detail.php?id_e={$info['id_e']}&page=3");
		} catch (Exception $ex) {
			$this->LastError->setLastError($ex->getMessage());
			$this->redirect("/connecteur/edition.php?id_ce=$id_ce");
		}                                
	}
        


	public function doEditionLibelle(){
		$recuperateur = new Recuperateur($_POST);
		$id_ce = $recuperateur->getInt('id_ce');
		$libelle = $recuperateur->get('libelle');

		try {
			$this->getConnecteurController()->editAction();
		} catch (Exception $ex) {
			$this->getLastError()->setLastError($ex->getMessage());
			$this->redirect("/connecteur/edition-libelle.php?id_ce=$id_ce");
		}
		$this->getLastMessage()->setLastMessage("Le connecteur « $libelle » a été modifié.");
		$this->redirect("/connecteur/edition.php?id_ce=$id_ce");
	}

	public function doEditionModif(){
		$recuperateur = new Recuperateur($_POST);
		$id_ce = $recuperateur->getInt('id_ce');
		$this->verifDroitOnConnecteur($id_ce);
		
		$fileUploader = new FileUploader();
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
		$donneesFormulaire->saveTab($recuperateur,$fileUploader,0);
		
		foreach($donneesFormulaire->getOnChangeAction() as $action) {	
			$this->ActionExecutorFactory->executeOnConnecteur($id_ce,$this->Authentification->getId(),$action);
		}
		
		$this->redirect("/connecteur/edition.php?id_ce=$id_ce");
	}

	public function recupFile(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$field = $recuperateur->get('field');
		$num = $recuperateur->getInt('num');
		
		$this->verifDroitOnConnecteur($id_ce);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
		$filePath = $donneesFormulaire->getFilePath($field,$num);
		if (!$filePath){
			$this->LastError->setLastError("Ce fichier n'existe pas");
			$this->redirect("/connecteur/edition.php?id_ce=$id_ce");
		}
		$fileName = $donneesFormulaire->getFileName($field,$num);
		
		header("Content-type: ".mime_content_type($filePath));
		header("Content-disposition: attachment; filename=\"$fileName\"");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
		readfile($filePath);
	}
	
	public function deleteFile(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$field = $recuperateur->get('field');
		$num = $recuperateur->getInt('num');
		
		$this->verifDroitOnConnecteur($id_ce);
		
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
		$donneesFormulaire->removeFile($field,$num);
		
		$this->redirect("/connecteur/edition-modif.php?id_ce=$id_ce");
	}

	public function deleteAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$this->verifDroitOnConnecteur($id_ce);		
		
		$this->connecteur_entite_info = $this->ConnecteurEntiteSQL->getInfo($id_ce);
		
		$this->page_title = "Suppression du connecteur  « {$this->connecteur_entite_info['libelle']} »";
		$this->template_milieu = "ConnecteurDelete";
		$this->renderDefault();
	}

	private function setConnecteurInfo(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		$this->verifDroitOnConnecteur($id_ce);
		$connecteur_entite_info = $this->ConnecteurEntiteSQL->getInfo($id_ce);
		$id_e = $connecteur_entite_info['id_e'];
		$entite_info = $this->EntiteSQL->getInfo($id_e);


		try {
			$donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
			$this->donneesFormulaire = $donneesFormulaire;
		} catch (Exception $e){
			$this->LastError->setLastError("Impossible de trouver la défintion pour le connecteur de type {$connecteur_entite_info['type']} ");
			$this->redirect("entite/detail.php?id_e=$id_e&page=3");
		}
		
		$this->inject = array('id_e'=>$id_e,'id_ce'=>$id_ce,'id_d'=>'','action'=>'');
		
		$this->my_role = "";
		

		
		if ($connecteur_entite_info['id_e']){
			$this->action = $this->DocumentTypeFactory->getEntiteDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
		} else {
			$this->action = $this->DocumentTypeFactory->getGlobalDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
		} 
		
		
		if (! $id_e){
			$entite_info['denomination'] = "Entité racine";
		}
		$this->entite_info = $entite_info;
		$this->connecteur_entite_info = $connecteur_entite_info;
		$this->id_ce = $id_ce;
		$this->id_e = $id_e;
	}

	public function editionModif(){
		$this->setConnecteurInfo();
		$this->page_title = "Configuration des connecteurs pour « {$this->entite_info['denomination']} »";
		$this->action_url = "connecteur/edition-modif-controler.php";
		$this->recuperation_fichier_url = "connecteur/recuperation-fichier.php?id_ce=".$this->id_ce;
		$this->suppression_fichier_url = "connecteur/supprimer-fichier.php?id_ce=".$this->id_ce;
		$this->page = 0;
		$this->externalDataURL = "connecteur/external-data.php" ;
		
		$this->template_milieu = "ConnecteurEditionModif";
		$this->renderDefault();
	}
	
	public function editionAction(){
		$this->setConnecteurInfo();
		$this->page_title = "Configuration des connecteurs pour « {$this->entite_info['denomination']} »";
		$this->recuperation_fichier_url = "connecteur/recuperation-fichier.php?id_ce=".$this->id_ce;
		$this->template_milieu = "ConnecteurEdition";
		$this->fieldDataList = $this->donneesFormulaire->getFieldDataListAllOnglet($this->my_role);
		$this->job_list = $this->WorkerSQL->getJobListWithWorkerForConnecteur($this->id_ce);
		$this->return_url = urlencode("connecteur/edition.php?id_ce={$this->id_ce}");
		
		$this->renderDefault();
	}
	
	public function newAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->getInt('id_e');
		$this->verifDroit($id_e, "entite:edition");
		
		$this->id_e = $id_e;		
		$this->all_connecteur_dispo = $this->getConnecteurDefinitionFile()->getAllByIdE($id_e);
		
		$this->page_title = "Ajout d'un connecteur";
		$this->template_milieu = "ConnecteurNew";
		$this->renderDefault();
	}
	
	public function editionLibelleAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_ce = $recuperateur->getInt('id_ce');
		
		$this->verifDroitOnConnecteur($id_ce);
		
		$this->connecteur_entite_info = $this->ConnecteurEntiteSQL->getInfo($id_ce);
		
		$this->page_title = "Modification du connecteur  « {$this->connecteur_entite_info['libelle']} »";
		$this->template_milieu = "ConnecteurEditionLibelle";
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

		$this->connecteur_entite_info = $this->ConnecteurEntiteSQL->getInfo($id_ce);

		$this->page_title = "Importer des données pour le connecteur  « {$this->connecteur_entite_info['libelle']} »";
		$this->template_milieu = "ConnecteurImport";
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
			$this->LastMessage->setLastMessage("Les données du connecteur ont été importées");
		} catch (Exception $e){
			$this->LastError->setLastError($e->getMessage());
		}

		$this->redirect("/connecteur/edition.php?id_ce=$id_ce");
	}

}