<?php

//Responsabilité: Appeller les bons objects qui connaissent l'emplacement des fichier de conf
//et construire un DocumentType
//(documents, entités, propriétés globales)
class DocumentTypeFactory {
	
	private $connecteurDefinitionFiles;
	private $fluxDefinitionFiles;
	
	public function __construct(
					ConnecteurDefinitionFiles $connecteurDefinitionFiles,
					FluxDefinitionFiles $fluxDefinitionFiles
	){
		$this->connecteurDefinitionFiles = $connecteurDefinitionFiles;
		$this->fluxDefinitionFiles = $fluxDefinitionFiles;
	}

	/**
	 * @param $id_e
	 * @param $id_connecteur
	 * @return DocumentType
	 * @throws Exception
	 */
	public function getDocumentType($id_e,$id_connecteur){
		if ($id_e) {
			return $this->getEntiteDocumentType($id_connecteur);
		} else {
			return $this->getGlobalDocumentType($id_connecteur);
		}
	}

	/**
	 * @param $id_connecteur
	 * @return DocumentType
	 * @throws Exception
	 */
	public function getGlobalDocumentType($id_connecteur){
		$connecteur_definition = $this->connecteurDefinitionFiles->getInfoGlobal($id_connecteur); 
		if (!$connecteur_definition){
			return new DocumentType($id_connecteur,$this->connecteurDefinitionFiles->getInfo('empty'));
		}
		return new DocumentType($id_connecteur,$connecteur_definition);
	}

	/**
	 * @param $id_connecteur
	 * @return DocumentType
	 * @throws Exception
	 */
	public function getEntiteDocumentType($id_connecteur){
		$connecteur_definition = $this->connecteurDefinitionFiles->getInfo($id_connecteur); 
		if (!$connecteur_definition){
			return new DocumentType($id_connecteur,$this->connecteurDefinitionFiles->getInfo('empty'));
		}
		return new DocumentType($id_connecteur,$connecteur_definition);
	}
	
	public function getFluxDocumentType($id_flux){
		$flux_definition = $this->getDocumentTypeArray($id_flux);
		if (!$flux_definition){
			return new DocumentType($id_flux,array());
		}
		return new DocumentType($id_flux,$flux_definition);
	}
	
	public function getDocumentTypeArray($id_flux){
		return $this->fluxDefinitionFiles->getInfo($id_flux);
	}
	
	public function getAllType(){
		static $result;
		
		if ($result){
			return $result;
		}
		$all_type = array();
		foreach ($this->fluxDefinitionFiles->getAll() as $id_flux => $properties){		
			$documentType = $this->getFluxDocumentType($id_flux);	
			$type = $documentType->getType();
			$all_type[$type][$id_flux] = $documentType->getName();
		}
		foreach($all_type as $type => $flux){
			asort($all_type[$type]);
		}
		asort($all_type);
		
		$result[DocumentType::TYPE_FLUX_DEFAULT] =  $all_type[DocumentType::TYPE_FLUX_DEFAULT];
		unset($all_type[DocumentType::TYPE_FLUX_DEFAULT]);
		return $result + $all_type;
	}
	
	public function isSuperTypePresent($type){
		$all = $this->getAllType();
		return isset($all[$type]);
	}

	public function isTypePresent($type){
		$all = $this->fluxDefinitionFiles->getAll();
		return isset($all[$type]);
	}
	
	public function getActionByRole($allDroit){
		foreach($allDroit as $droit){
			$r = explode(":",$droit);
			$allType[$r[0]] = true;
		}
		$allType = array_keys($allType);
		foreach($allType as $typeName){
			try {
				$action = $this->getFluxDocumentType($typeName)->getAction();
			} catch (Exception $e ){
				continue;
			}
			$a_wf = $action->getWorkflowAction();
			if ($a_wf){
				$result[$typeName] = $a_wf;
			} 
		}
		return $result;
	}
	
	public function getAllDroit(){
		$list_droit = array();
		foreach ($this->fluxDefinitionFiles->getAll() as $id_flux => $properties){
			$documentType = $this->getFluxDocumentType($id_flux);
			$list_droit = array_merge($list_droit,$documentType->getListDroit());
		}
		sort($list_droit);
		return $list_droit;
	}
	
}