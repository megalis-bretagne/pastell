<?php
class ActionExecutorFactory {
	
	const ACTION_FOLDERNAME = "action"; 
	
	private $extensions;
	private $objectInstancier;
	
	private $lastMessage;
	private $lastMessageString;

    private $recuperateur;


    public function __construct(Extensions $extensions, ObjectInstancier $objectInstancier){
		$this->extensions = $extensions;
		$this->objectInstancier = $objectInstancier;
	}
	
	public function getLastMessage(){
		return $this->lastMessage;
	}
	
	public function getLastMessageString() {
		if (isset($this->lastMessageString) && ($this->lastMessageString !== false)) {
			return $this->lastMessageString;
		}
		return $this->getLastMessage();
	}
	
	/**
	 * @return JobManager
	 */
	public function getJobManager(){
		return $this->objectInstancier->JobManager;
	}
	
	public function executeOnConnecteur($id_ce,$id_u,$action_name, $from_api=false, $action_params=array(), $id_worker=0){
		try {
            /** @var WorkerSQL $workerSQL */
            $workerSQL = $this->objectInstancier->getInstance("WorkerSQL");
            $id_worker_en_cours  = $workerSQL->getActionEnCoursForConnecteur($id_ce, $action_name);
            if ($id_worker_en_cours != $id_worker){
                throw new Exception("Une action est déjà en cours de réalisation sur ce connecteur");
            }
			$result = $this->executeOnConnecteurThrow($id_ce,$id_u,$action_name, $from_api, $action_params);
		} catch(Exception $e){
			$this->lastMessage = $e->getMessage();
			$result =  false;	
		} 
		$this->getJobManager()->setJobForConnecteur($id_ce, $action_name,$this->getLastMessageString());
		return $result;
	}

	public function executeOnDocument($id_e,$id_u,$id_d,$action_name,$id_destinataire=array(),$from_api = false, $action_params=array(),$id_worker = 0){
		try {
			/** @var WorkerSQL $workerSQL */
			$workerSQL = $this->objectInstancier->getInstance("WorkerSQL");
			if ($workerSQL->getActionEnCours($id_e,$id_d) != $id_worker){
				throw new Exception("Une action est déjà en cours de réalisation sur ce document");
			}
			
			$result = $this->executeOnDocumentThrow($id_d, $id_e, $id_u,$action_name,$id_destinataire,$from_api, $action_params,$id_worker);
		} catch (Exception $e){
			if (LOG_ACTION_EXECUTOR_FACTORY_ERROR) {
				$this->objectInstancier->Journal->add(Journal::DOCUMENT_ACTION_ERROR, $id_e, $id_d, $action_name, $e->getMessage());
			}
			$this->lastMessage = $e->getMessage();
			$result = false;	
		}	
		$this->getJobManager()->setJobForDocument($id_e, $id_d,$this->getLastMessageString());
		return $result;
	}
	
	public function displayChoice($id_e,$id_u,$id_d,$action_name,$from_api,$field,$page = 0){
		
		$infoDocument = $this->objectInstancier->Document->getInfo($id_d);
		$documentType = $this->objectInstancier->DocumentTypeFactory->getFluxDocumentType($infoDocument['type']);
		
		$action_class_name = $this->getActionClassName($documentType, $action_name);		
		
		$this->loadDocumentActionFile($infoDocument['type'],$action_class_name);
		$actionClass = $this->getInstance($action_class_name,$id_e,$id_u,$action_name);
		$actionClass->setDocumentId($infoDocument['type'], $id_d);
		$actionClass->setFromAPI($from_api);
		$actionClass->field = $field;
		$actionClass->page = $page;
		
		
		if ($from_api){
			$result = $actionClass->displayAPI();
		} else {				
			$result = $actionClass->display();
		}		
		return $result;
	}
	
	public function getChoiceForSearch($id_e,$id_u,$type,$action_name,$field){
		$documentType = $this->objectInstancier->DocumentTypeFactory->getFluxDocumentType($type);
		$action_class_name = $this->getActionClassName($documentType, $action_name);
		$this->loadDocumentActionFile($type,$action_class_name);
		$actionClass = $this->getInstance($action_class_name,$id_e,$id_u,$action_name);
		$actionClass->field = $field;
		$actionClass->setDocumentId($type, 0);
		
		$result = $actionClass->displayChoiceForSearch();
		return $result;
		
	}
	
	public function isChoiceEnabled($id_e,$id_u,$id_d,$action_name){
		
		$infoDocument = $this->objectInstancier->Document->getInfo($id_d);
	
		
		$documentType = $this->objectInstancier->DocumentTypeFactory->getFluxDocumentType($infoDocument['type']);
		
		$action_class_name = $this->getActionClassName($documentType, $action_name);

		
		$this->loadDocumentActionFile($infoDocument['type'],$action_class_name);
		$actionClass = $this->getInstance($action_class_name,$id_e,$id_u,$action_name);
		$actionClass->setDocumentId($infoDocument['type'], $id_d);
		return $actionClass->isEnabled();
	}
	

	//TODO simplifier le action_name peut être déduit du field
	public function displayChoiceOnConnecteur($id_ce,$id_u,$action_name,$field,$is_api = false){
		$connecteur_entite_info = $this->objectInstancier->ConnecteurEntiteSQL->getInfo($id_ce);
		if ($connecteur_entite_info['id_e']){				
			$documentType = $this->objectInstancier->documentTypeFactory->getEntiteDocumentType($connecteur_entite_info['id_connecteur']);
		} else {
			$documentType = $this->objectInstancier->documentTypeFactory->getGlobalDocumentType($connecteur_entite_info['id_connecteur']);
		}
		
		$action_class_name = $this->getActionClassName($documentType, $action_name);
		$this->loadConnecteurActionFile($connecteur_entite_info['id_connecteur'],$action_class_name);
		
		$actionClass = $this->getInstance($action_class_name,$connecteur_entite_info['id_e'],$id_u,$action_name);
		$actionClass->setConnecteurId($connecteur_entite_info['id_connecteur'], $id_ce);
		$actionClass->setField($field);
		try {
		    if ($is_api) {
                $result = $actionClass->displayAPI();
            } else {
                $result = $actionClass->display();
            }
		} catch(Exception $e) {
			$this->lastMessage = $e->getMessage();
			return false;
		}		
		$this->lastMessage = $actionClass->getLastMessage();		
		return $result;		
	}
	
	public function goChoice($id_e,$id_u,$id_d,$action_name,$from_api,$field,$page = 0){
		$infoDocument = $this->objectInstancier->Document->getInfo($id_d);
		$documentType = $this->objectInstancier->DocumentTypeFactory->getFluxDocumentType($infoDocument['type']);
		
		$action_class_name = $this->getActionClassName($documentType, $action_name);		
		$this->loadDocumentActionFile($infoDocument['type'],$action_class_name);
		
		$actionClass = $this->getInstance($action_class_name,$id_e,$id_u,$action_name);
		$actionClass->setDocumentId($infoDocument['type'], $id_d);
		$actionClass->setFromAPI($from_api);
		$actionClass->field = $field;
		$actionClass->page = $page;

		$result = $actionClass->go();
		if ($from_api){
			$result['result'] = "ok";
			/** @var JSONoutput $jsonOutput */
			$jsonOutput = $this->objectInstancier->getInstance('JSONoutput');
			$jsonOutput->sendJson($result);
		} else {
			$actionClass->redirectToFormulaire();
		}
	}
	
	public function goChoiceOnConnecteur($id_ce,$id_u,$action_name,$field,$is_api = false,$post_data = false){

	    $connecteur_entite_info = $this->objectInstancier->ConnecteurEntiteSQL->getInfo($id_ce);

	    if ($connecteur_entite_info['id_e']){
			$documentType = $this->objectInstancier->documentTypeFactory->getEntiteDocumentType($connecteur_entite_info['id_connecteur']);
		} else {
			$documentType = $this->objectInstancier->documentTypeFactory->getGlobalDocumentType($connecteur_entite_info['id_connecteur']);
		}
		
		$action_class_name = $this->getActionClassName($documentType, $action_name);
		$this->loadConnecteurActionFile($connecteur_entite_info['id_connecteur'],$action_class_name);

		/** @var ChoiceActionExecutor $actionClass */
		$actionClass = $this->getInstance($action_class_name,$connecteur_entite_info['id_e'],$id_u,$action_name);
		$actionClass->setConnecteurId($connecteur_entite_info['id_connecteur'], $id_ce);
		$actionClass->setField($field);
		if ($post_data){
		    $actionClass->setRecuperateur(new Recuperateur($post_data));
        }
		try {	
			$actionClass->go();
		} catch(Exception $e){
			$this->lastMessage = $e->getMessage() ;
		}
		if (! $is_api) {
            $actionClass->redirectToConnecteurFormulaire();
        }
	}
	
	public function executeOnDocumentThrow($id_d,$id_e,$id_u,$action_name,$id_destinataire,$from_api, $action_params,$id_worker){
		$actionClass = $this->getActionClass($id_d, $id_e, $id_u, $action_name, $id_destinataire, $from_api, $action_params,$id_worker);
		$result = $actionClass->go();
		$this->lastMessageString = $actionClass->getLastMessageString();
		$this->lastMessage = $actionClass->getLastMessage();		
		return $result;						
	}
	
	private function getActionClass($id_d,$id_e,$id_u,$action_name,$id_destinataire,$from_api, $action_params,$id_worker){
		$infoDocument = $this->objectInstancier->Document->getInfo($id_d);
		$documentType = $this->objectInstancier->DocumentTypeFactory->getFluxDocumentType($infoDocument['type']);
		
		$action_class_name = $this->getActionClassName($documentType, $action_name);
		$this->loadDocumentActionFile($infoDocument['type'],$action_class_name);
		
		$actionClass = $this->getInstance($action_class_name,$id_e,$id_u,$action_name);
		$actionClass->setDocumentId($infoDocument['type'], $id_d);
		$actionClass->setDestinataireId($id_destinataire);
		$actionClass->setActionParams($action_params);
		$actionClass->setFromAPI($from_api);
		$actionClass->setIdWorker($id_worker);
		return $actionClass;
	}
	
	private function executeOnConnecteurThrow($id_ce,$id_u,$action_name, $from_api=false, $action_params=array()){
		$connecteur_entite_info = $this->objectInstancier->ConnecteurEntiteSQL->getInfo($id_ce);
		if ($connecteur_entite_info['id_e']){				
			$documentType = $this->objectInstancier->documentTypeFactory->getEntiteDocumentType($connecteur_entite_info['id_connecteur']);
		} else {
			$documentType = $this->objectInstancier->documentTypeFactory->getGlobalDocumentType($connecteur_entite_info['id_connecteur']);
		}
		
		$action_class_name = $this->getActionClassName($documentType, $action_name);
		$this->loadConnecteurActionFile($connecteur_entite_info['id_connecteur'],$action_class_name);
		
		$actionClass = $this->getInstance($action_class_name,$connecteur_entite_info['id_e'],$id_u,$action_name);
		$actionClass->setConnecteurId($connecteur_entite_info['id_connecteur'], $id_ce);
		$actionClass->setActionParams($action_params);
		$actionClass->setFromAPI($from_api);
		$result = $actionClass->go();
		$this->lastMessageString = $actionClass->getLastMessageString();
		$this->lastMessage = $actionClass->getLastMessage();		
		return $result;		
		
	}
	
	private function getActionClassName(DocumentType $documentType,$action_name){
		if ($action_name == ActionPossible::FATAL_ERROR_ACTION){
			return "FatalError";
		}
		$theAction = $documentType->getAction();		
		$action_class_name = $theAction->getActionClass($action_name);
		if (!$action_class_name){
			throw new Exception("L'action $action_name n'existe pas.");
		}
		return $action_class_name;
	}
	
	private function getInstance($action_class_name,$id_e,$id_u,$action_name){
		$actionClass = $this->objectInstancier->newInstance($action_class_name);
		$actionClass->setEntiteId($id_e);
		$actionClass->setUtilisateurId($id_u);
		$actionClass->setAction($action_name);
		return $actionClass;
	}

	private function loadConnecteurActionFile($id_connecteur, $action_class_name){
		$connecteur_path = $this->extensions->getConnecteurPath($id_connecteur);
		$action_class_file = "$connecteur_path/".self::ACTION_FOLDERNAME."/$action_class_name.class.php";
		if ( ! file_exists($action_class_file)){
			throw new Exception("Le fichier $action_class_name est introuvable");
		} 
		require_once($action_class_file);
	}	
	
	
	private function loadDocumentActionFile($flux, $action_class_name){
		$action_class_file = $this->getFluxActionPath($flux, $action_class_name);
		if (! $action_class_file){				
			throw new Exception( "Le fichier $action_class_name est manquant");
		}
		require_once($action_class_file);
	}
	
	public function getFluxActionPath($flux,$action_class_name){
		$module_path = $this->extensions->getModulePath($flux);
		$action_class_file = "$module_path/".self::ACTION_FOLDERNAME."/$action_class_name.class.php";
		
		if (file_exists($action_class_file)){
			return $action_class_file;
		}
		$action_class_file = PASTELL_PATH."/".self::ACTION_FOLDERNAME."/$action_class_name.class.php";
		if (file_exists($action_class_file )){
			return $action_class_file;
		}		
		foreach ($this->extensions->getAllModule() as $module_id => $module_path){
			$action_path = "$module_path/".self::ACTION_FOLDERNAME."/$action_class_name.class.php";
			if (file_exists($action_path)){
				return $action_path;
			}
		}
		return false;
	}

	public function getAllActionClass(){
		$action_class_file = PASTELL_PATH."/".self::ACTION_FOLDERNAME;
		$result = array();
		foreach(glob($action_class_file."/*.class.php") as $action_class_path){
			preg_match("#/([^/]+).class.php$#",$action_class_path,$matches);
			$result[] = $matches[1];
		}

		foreach ($this->extensions->getAllModule() as $module_id => $module_path){
			foreach(glob($module_path."/".self::ACTION_FOLDERNAME."/*.class.php") as $action_class_path){
				preg_match("#/([^/]+).class.php$#",$action_class_path,$matches);
				$result[] = $matches[1];
			}
		}
		return $result;
	}
	
	public function executeLotDocument($id_e,$id_u,array $all_id_d,$action_name,$id_destinataire=array(),$from_api=false,$action_params=array(),$id_worker=0){
		$actionClass = $this->getActionClass($all_id_d[0], $id_e, $id_u, $action_name, $id_destinataire, $from_api, $action_params,$id_worker);
		$actionClass->goLot($all_id_d);
	}
	
}