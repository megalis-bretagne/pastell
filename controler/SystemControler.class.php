<?php
class SystemControler extends PastellControler {
	
	public function indexAction(){
		$this->verifDroit(0,"system:lecture");

		$this->droitEdition = $this->hasDroit(0, "system:edition");
		$recuperateur=new Recuperateur($_GET);
		$page_number = $recuperateur->getInt('page_number');
		
		switch($page_number){
			case 1:
				$this->fluxAction(); break;	
			case 2:
				$this->fluxDefAction(); break;
			case 3:
				$this->extensionListAction(); break;
			case 4:
				$this->ConnecteurListAction(); break;
			case 0:
			default: $this->environnementAction(); break;
		}
		
		$this->onglet_tab = array("Tests du syst�me","Flux","D�finition des flux","Extensions","Connecteurs");
		$this->page_number = $page_number;
		$this->template_milieu = "SystemIndex";
		$this->page_title = "Environnement syst�me";
		$this->renderDefault();
	}
	
	public function getPageNumber($page_name){
		$tab_number = array("system"=> 0,
								"flux" => 1,
								"definition" => 2,
								"extensions" => 3,
								"connecteurs" => 4);
		return $tab_number[$page_name];
	}
	
	private function environnementAction(){
		$this->checkExtension = $this->VerifEnvironnement->checkExtension();
		$this->checkPHP = $this->VerifEnvironnement->checkPHP();
		$this->checkWorkspace = $this->VerifEnvironnement->checkWorkspace();
		$this->checkModule = $this->VerifEnvironnement->checkModule();
		$this->valeurMinimum = array(
			"PHP" => $this->checkPHP['min_value'],
			"OpenSSL" => '1.0.0a',
		);
		$this->manifest_info = $this->ManifestFactory->getPastellManifest()->getInfo();
		$cmd =  OPENSSL_PATH . " version";
		$openssl_version = `$cmd`;
		$this->valeurReel = array('OpenSSL' =>  $openssl_version, 'PHP' => $this->checkPHP['environnement_value']); 

		$this->commandeTest = $this->VerifEnvironnement->checkCommande(array());
		$this->onglet_content = "SystemEnvironnement";
	}
	
	private function fluxAction(){
		$all_flux = array();
		$all_connecteur_type = $this->ConnecteurDefinitionFiles->getAllType();
		$all_type_entite = array_keys(Entite::getAllType());
		foreach($this->FluxDefinitionFiles->getAll() as $id_flux => $flux){
			$documentType = $this->DocumentTypeFactory->getFluxDocumentType($id_flux);
			$all_flux[$id_flux]['nom'] = $documentType->getName();
			$all_flux[$id_flux]['type'] = $documentType->getType();
			$document_type_array = $this->DocumentTypeFactory->getDocumentTypeArray($id_flux);
			$all_flux[$id_flux]['is_valide'] = 
					$this->DocumentTypeValidation->validate($id_flux,
														$document_type_array,
														$all_connecteur_type,
														$all_type_entite);
		}
		$this->all_flux = $all_flux;
		$this->onglet_content = "SystemFlux";
	}
	
	public function fluxDefAction(){
		$this->flux_definition = $this->DocumentTypeValidation->getModuleDefinition();
		$this->onglet_content = "SystemFluxDef";
	}
	
	public function extensionListAction(){
		$this->all_extensions = $this->Extensions->getAll();
		$this->onglet_content = "SystemExtensionList";
		$this->pastell_manifest = $this->ManifestFactory->getPastellManifest()->getInfo();
	}
	
	public function connecteurListAction(){
		$this->all_connecteur_entite = $this->ConnecteurDefinitionFiles->getAll();
		$this->all_connecteur_globaux = $this->ConnecteurDefinitionFiles->getAllGlobal();
		$this->onglet_content = "SystemConnecteurList";
	}
	
	public function extensionAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->get("id_extension");
		$extension_info = $this->Extensions->getInfo($id_e);
		
		$this->extension_info = $extension_info;
		$this->template_milieu = "SystemExtension";
		$this->page_title = "Extension {$extension_info['nom']}";
	 			
		$this->renderDefault();
	}

	public function extensionEditionAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->get("id_extension",0);
		$this->verifDroit(0,"system:edition");
		$extension_info = $this->ExtensionSQL->getInfo($id_e);
		if (!$extension_info){
			$extension_info = array('id_e'=>0,'path'=>'');
		}
		$this->extension_info = $extension_info;
		$this->template_milieu = "SystemExtentionEdition";
		$this->page_title = "�dition d'une extension";
		$this->renderDefault();
	}
	
	public function doExtensionEditionAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->get("id_e");
		$path = $recuperateur->get("path");
		if (file_exists($path)){
			$this->ExtensionSQL->edit($id_e,$path);
			$this->LastMessage->setLastMessage("Extension �dit�e");
		} else {
			$this->LastError->setLastError("Le chemin � $path � n'existe pas sur le syst�me de fichier");
		}
		$this->redirect("/system/index.php?page_number=".$this->getPageNumber('extensions'));
	}
	
	public function extensionDeleteAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->get("id_e");
		$this->ExtensionSQL->delete($id_e);
		$this->LastMessage->setLastMessage("Extension supprim�e");
		$this->redirect("/system/index.php?page_number=".$this->getPageNumber('extensions'));
	}

	public function fluxDetailAction(){
		$recuperateur=new Recuperateur($_GET);
		$id = $recuperateur->get('id');		
		$documentType = $this->DocumentTypeFactory->getFluxDocumentType($id);
		$name = $documentType->getName();
		$this->description = $documentType->getDescription();
		$this->all_connecteur = $documentType->getConnecteur();
		$all_action = array();
		$action = $documentType->getAction();
		$action_list = $action->getAll();
		sort($action_list);
		foreach($action_list as $action_name){
			$class_name = $action->getActionClass($action_name);
			$all_action[] = array(
				'id'=> $action_name,
				'name' => $action->getActionName($action_name),
				'do_name' => $action->getDoActionName($action_name),
				'class' => $class_name,
				'path' => $this->ActionExecutorFactory->getFluxActionPath($id,$class_name),
				'action_auto' => $action->getActionAutomatique($action_name)
			); 	
		}
		$this->all_action = $all_action;
		
		$formulaire = $documentType->getFormulaire();
		
		$allFields = $formulaire->getAllFields();
		$form_fields = array();
		foreach($allFields as $field){
			$form_fields[$field->getName()] = $field->getAllProperties();
			
		}
		$this->formulaire_fields = $form_fields;		
		$all_connecteur_type = $this->ConnecteurDefinitionFiles->getAllType();
		$all_type_entite = array_keys(Entite::getAllType());
		
		
		$this->document_type_is_validate = $this->DocumentTypeValidation->validate($id,
			$this->DocumentTypeFactory->getDocumentTypeArray($id),$all_connecteur_type,$all_type_entite);
		$this->validation_error =  $this->DocumentTypeValidation->getLastError();
		$this->page_title = "D�tail du flux � $name �";
		$this->template_milieu = "SystemFluxDetail";
		$this->renderDefault();
	}
	
	public function mailTestAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur=new Recuperateur($_POST);
		$email = $recuperateur->get("email");

		$this->ZenMail->setEmetteur("Pastell",PLATEFORME_MAIL);
		
		$this->ZenMail->setDestinataire($email);
		$this->ZenMail->setSujet("[Pastell] Mail de test");
		
		$this->ZenMail->resetAttachment();
		$this->ZenMail->addAttachment("exemple.pdf", __DIR__."/../data-exemple/exemple.pdf");
		
		$this->ZenMail->setContenu(PASTELL_PATH . "/mail/test.php",array());
		$this->ZenMail->send();
		
		$this->LastMessage->setLastMessage("Un email a �t� envoy� � l'adresse  : ".get_hecho($email));
		$this->redirect('system/index.php?page_number='.$this->getPageNumber('system'));		
	}
	
	
	
}