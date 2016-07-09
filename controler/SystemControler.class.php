<?php
class SystemControler extends PastellControler {

	/**
	 * @return Extensions
	 */
	public function getExtensions(){
		return $this->getInstance("Extensions");
	}

	/**
	 * @return ExtensionSQL
	 */
	public function getExtensionSQL(){
		return $this->getInstance("ExtensionSQL");
	}
	
	public function indexAction(){
		$this->verifDroit(0,"system:lecture");

		$this->{'droitEdition'}= $this->hasDroit(0, "system:edition");
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
				$this->connecteurListAction(); break;
			case 0:
			default: $this->environnementAction(); break;
		}
		
		$this->{'onglet_tab'}= array("Tests du système","Flux","Définition des flux","Extensions","Connecteurs");
		$this->{'page_number'}= $page_number;
		$this->{'template_milieu'}= "SystemIndex";
		$this->{'page_title'}= "Environnement système";
		$this->renderDefault();
	}

	/**
	 * @return ManifestFactory
	 */
	protected function getManifestFactory(){
		return $this->getInstance("ManifestFactory");
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

		/** @var VerifEnvironnement $verifEnvironnement */
		$verifEnvironnement = $this->getInstance("VerifEnvironnement");

		$this->{'checkExtension'}=$verifEnvironnement->checkExtension();
		$this->{'checkPHP'}= $verifEnvironnement->checkPHP();
		$this->{'checkWorkspace'}= $verifEnvironnement->checkWorkspace();
		$this->{'checkModule'}= $verifEnvironnement->checkModule();
		$this->{'valeurMinimum'}= array(
			"PHP" => $this->{'checkPHP'}['min_value'],
			"OpenSSL" => '1.0.0a',
		);
		$this->{'manifest_info'}= $this->getManifestFactory()->getPastellManifest()->getInfo();
		$cmd =  OPENSSL_PATH . " version";
		$openssl_version = `$cmd`;
		$this->{'valeurReel'}= array(
			'OpenSSL' =>  $openssl_version,
			'PHP' => $this->{'checkPHP'}['environnement_value']
		);

		$this->{'commandeTest'}= $verifEnvironnement->checkCommande(array('dot'));
		
		$this->{'connecteur_manquant'}= $this->getConnecteurFactory()->getManquant();
		$this->{'document_type_manquant'}= $this->getTypeDocumentManquant();
	
		$this->{'onglet_content'}= "SystemEnvironnement";
	}
	
	
	private function getTypeDocumentManquant(){
		$result = array();
		$document_type_list = $this->getDocument()->getAllType();
		$module_list = $this->getExtensions()->getAllModule();
		foreach($document_type_list as $document_type){
			if (empty($module_list[$document_type])){
				$result[] = $document_type;
			}
		}
		return $result;
	}
	
	private function fluxAction(){
		$all_flux = array();
		$documentTypeValidation = $this->getDocumentTypeValidation();
		foreach($this->getFluxDefinitionFiles()->getAll() as $id_flux => $flux){
			$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id_flux);
			$all_flux[$id_flux]['nom'] = $documentType->getName();
			$all_flux[$id_flux]['type'] = $documentType->getType();
			$definition_path =$this->getFluxDefinitionFiles()->getDefinitionPath($id_flux);
			$all_flux[$id_flux]['is_valide'] = $documentTypeValidation->validate($definition_path);
		}
		$this->{'all_flux'}= $all_flux;
		$this->{'onglet_content'}= "SystemFlux";
	}


	private function getDocumentTypeValidation(){
		/** @var ActionExecutorFactory $actionExecutorFactory */
		$actionExecutorFactory = $this->{'ActionExecutorFactory'};
		$all_action_class = $actionExecutorFactory->getAllActionClass();

		$all_connecteur_type = $this->getConnecteurDefinitionFiles()->getAllType();
		$all_type_entite = array_keys(Entite::getAllType());


		$connecteur_type_action_class_list = $this->{'ConnecteurTypeFactory'}->getAllActionExecutor();

		/** @var DocumentTypeValidation $documentTypeValidation */
		$documentTypeValidation = $this->{'DocumentTypeValidation'};
		$documentTypeValidation->setConnecteurTypeList($all_connecteur_type);
		$documentTypeValidation->setEntiteTypeList($all_type_entite);
		$documentTypeValidation->setActionClassList($all_action_class);
		$documentTypeValidation->setConnecteurTypeActionClassList($connecteur_type_action_class_list);
		return $documentTypeValidation;
	}


	public function fluxDetailAction(){
		$recuperateur=new Recuperateur($_GET);
		$id = $recuperateur->get('id');
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id);
		$name = $documentType->getName();
		$this->{'description'}= $documentType->getDescription();
		$this->{'all_connecteur'}= $documentType->getConnecteur();
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
				'path' => $this->getActionExecutorFactory()->getFluxActionPath($id,$class_name),
				'action_auto' => $action->getActionAutomatique($action_name)
			);
		}
		$this->{'all_action'}= $all_action;

		$formulaire = $documentType->getFormulaire();

		$allFields = $formulaire->getAllFields();
		$form_fields = array();
		/** @var Field $field */
		foreach($allFields as $field){
			$form_fields[$field->getName()] = $field->getAllProperties();

		}
		$this->{'formulaire_fields'}= $form_fields;

		$document_type_is_validate = false;
		$validation_error = false;
		try {
			$document_type_is_validate = $this->isDocumentTypeValid($id);
		} catch (Exception $e) {
			$validation_error = $this->getDocumentTypeValidation()->getLastError();
		}

		$this->{'document_type_is_validate'}= $document_type_is_validate;
		$this->{'validation_error'}= $validation_error;

		$this->{'page_title'}= "Détail du flux « $name »";
		$this->{'template_milieu'}= "SystemFluxDetail";
		$this->renderDefault();
	}

	public function fluxDefAction(){
		$this->{'flux_definition'}= $this->getDocumentTypeValidation()->getModuleDefinition();
		$this->{'onglet_content'}= "SystemFluxDef";
	}
	
	public function extensionListAction(){
		$this->{'all_extensions'}= $this->extensionList();
		$this->{'onglet_content'}= "SystemExtensionList";
		$this->{'pastell_manifest'}= $this->getManifestFactory()->getPastellManifest()->getInfo();
		$this->{'extensions_graphe'}= $this->getExtensions()->creerGraphe();
	}
	
	public function connecteurListAction(){
		$this->{'all_connecteur_entite'}= $this->getConnecteurDefinitionFiles()->getAll();
		$this->{'all_connecteur_globaux'}= $this->getConnecteurDefinitionFiles()->getAllGlobal();
		$this->{'onglet_content'}= "SystemConnecteurList";
	}

	public function extensionList(){
		/** @var ExtensionAPIController $extensionController */
		$extensionController = $this->getAPIController('Extension');
		$result = $extensionController->listAction();
		return $result['result'];
	}

	public function extensionAction(){
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->get("id_extension");
		$extension_info = $this->getExtensions()->getInfo($id_e);
		
		$this->{'extension_info'}= $extension_info;
		$this->{'template_milieu'}= "SystemExtension";
		$this->{'page_title'}= "Extension « {$extension_info['nom']} »";
	 			
		$this->renderDefault();
	}

	public function extensionEditionAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur = new Recuperateur($_GET);
		$id_e = $recuperateur->get("id_extension",0);
		$extension_info = $this->getExtensionSQL()->getInfo($id_e);
		if (!$extension_info){
			$extension_info = array('id_e'=>0,'path'=>'');
		}
		$this->{'extension_info'}= $extension_info;
		$this->{'template_milieu'}= "SystemExtentionEdition";
		$this->{'page_title'}= "Édition d'une extension";
		$this->renderDefault();
	}

	public function doExtensionEditionAction(){
		try {
			/** @var ExtensionAPIController $extensionController */
			$extensionController = $this->getAPIController('Extension');
			$extensionController->editAction();
			$this->setLastMessage("Extension éditée");
		} catch (Exception $e){
			$this->setLastError($e->getMessage());
		}

		$this->redirect("/System/index?page_number=".$this->getPageNumber('extensions'));
	}

	public function extensionDeleteAction(){
		try {
			/** @var ExtensionAPIController $extensionController */
			$extensionController = $this->getAPIController('Extension');
			$extensionController->deleteAction();
			$this->setLastMessage("Extension supprimée");
		} catch (Exception $e){
			$this->setLastError($e->getMessage());
		}
		$this->redirect("/System/index?page_number=".$this->getPageNumber('extensions'));
	}
	

	public function isDocumentTypeValid($id_flux){
		$documentTypeValidation = $this->getDocumentTypeValidation();
		$definition_path =$this->getFluxDefinitionFiles()->getDefinitionPath($id_flux);

		if (! $documentTypeValidation->validate($definition_path)){
			throw new Exception(implode("\n",$this->getDocumentTypeValidation()->getLastError())) ;
		}
		return true;
	}

	public function mailTestAction(){
		$this->verifDroit(0,"system:edition");
		$recuperateur=new Recuperateur($_POST);
		$email = $recuperateur->get("email");
		

		$this->getZenMail()->setEmetteur("Pastell",PLATEFORME_MAIL);
		
		$this->getZenMail()->setDestinataire($email);
		$this->getZenMail()->setSujet("[Pastell] Mail de test");
		
		$this->getZenMail()->resetAttachment();
		$this->getZenMail()->addAttachment("exemple.pdf", __DIR__."/../data-exemple/exemple.pdf");
		
		$this->getZenMail()->setContenu(PASTELL_PATH . "/mail/test.php",array());
		$this->getZenMail()->send();
		
		$this->setLastMessage("Un email a été envoyé à l'adresse  : ".get_hecho($email));
		$this->redirect('System/index?page_number='.$this->getPageNumber('system'));
	}
	
}
