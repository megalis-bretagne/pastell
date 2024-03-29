<?php

class ActionPossible {
	
	const FATAL_ERROR_ACTION = 'fatal-error';
	
	private $lastBadRule;
	
	private $documentActionEntite;
	private $documentEntite;
	/** @var RoleUtilisateur  */
	private $roleUtilisateur;
	/** @var DocumentTypeFactory */
	private $documentTypeFactory;
	/** @var Document */
	private $document;
	private $entiteSQL;
	private $donneesFormulaireFactory;
	private $connecteurEntiteSQL;
	
	public function __construct(ObjectInstancier $objectInstancier){
		$this->document = $objectInstancier->getInstance("Document");
		$this->documentActionEntite = $objectInstancier->getInstance("DocumentActionEntite");
		$this->documentEntite = $objectInstancier->getInstance("DocumentEntite");
		$this->roleUtilisateur = $objectInstancier->getInstance("RoleUtilisateur");
		$this->entiteSQL = $objectInstancier->getInstance("EntiteSQL");
		$this->documentTypeFactory = $objectInstancier->getInstance("DocumentTypeFactory");
		$this->donneesFormulaireFactory = $objectInstancier->getInstance("DonneesFormulaireFactory");
		$this->connecteurEntiteSQL = $objectInstancier->getInstance("ConnecteurEntiteSQL");
	}

	public function getLastBadRule(){
		return $this->lastBadRule;
	}

	
	public function isActionPossible($id_e,$id_u,$id_d,$action_name){
		$type_document = $this->getTypeDocument($id_d);

		if ($action_name == self::FATAL_ERROR_ACTION){
			return $this->verifDroitUtilisateur($id_e,$id_u,"$type_document:edition");
		}
		return $this->internIsActionPossible($id_e, $id_u, $id_d, $action_name,$type_document);
	}
	
	public function isCreationPossible($id_e,$id_u,$type_document){
		if ( ! $id_e ){
			return false;
		}
		$entite_info = $this->entiteSQL->getInfo($id_e);
		
		if (! $entite_info['is_active']){
			return false;
		}
		
		return $this->internIsActionPossible($id_e, $id_u, 0, Action::CREATION, $type_document);
	}

	public function getActionPossible($id_e,$id_u,$id_d){

		$type_document = $this->getTypeDocument($id_d);
		
		$action = $this->getAction($type_document);
		$possible = array();

		foreach($action->getAll() as $action_name){
			if ($this->isActionPossible($id_e,$id_u,$id_d,$action_name)){
				$possible[] = $action_name;
			}
		}

		return $possible;
	}
	
	public function getActionPossibleLot($id_e,$id_u,$id_d){
		$action_possible_list = $this->getActionPossible($id_e,$id_u,$id_d);
		
		$type_document = $this->getTypeDocument($id_d);
		$action = $this->getAction($type_document);
				
		$result = array();
		foreach($action_possible_list as $action_possible ){
			if ($action_possible=='modification'){
				continue;
			}
			if ($action->isPasDansUnLot($action_possible)){
				continue;
			}
			$result[] = $action_possible;
		}
		return $result;
	}

	/**
	 * @param $id_e
	 * @param $id_connecteur
	 * @return DocumentType
	 */
	private function getConnecteurDocumentType($id_e,$id_connecteur){		
		if ($id_e){
			$documentType = $this->documentTypeFactory->getEntiteDocumentType($id_connecteur);
		} else {
			$documentType = $this->documentTypeFactory->getGlobalDocumentType($id_connecteur);
		}
		return $documentType;
	}
	
	public function getActionPossibleOnConnecteur($id_ce,$id_u){
		$connecteur_entite_info = $this->connecteurEntiteSQL->getInfo($id_ce);		
		$documentType = $this->getConnecteurDocumentType($connecteur_entite_info['id_e'],$connecteur_entite_info['id_connecteur']);
		
		$action = $documentType->getAction();
		$possible = array();
		foreach($action->getAll() as $action_name){
			if ($this->isActionPossibleOnConnecteur($id_ce,$id_u,$action_name)){
				$possible[] = $action_name;
			}
		}
		return $possible;
	}
	
	public function isActionPossibleOnConnecteur($id_ce,$id_u,$action_name){		
		$connecteur_entite_info = $this->connecteurEntiteSQL->getInfo($id_ce);		
		$documentType = $this->getConnecteurDocumentType($connecteur_entite_info['id_e'],$connecteur_entite_info['id_connecteur']);		
		return $this->internIsActionPossible($connecteur_entite_info['id_e'],$id_u,$connecteur_entite_info['id_e'],$action_name,$documentType);		
	}
	
	private function getTypeDocument($id_d){
		$infoDocument = $this->document->getInfo($id_d);
		return $infoDocument['type'];
	}
	
	private function internIsActionPossible($id_e,$id_u,$id_d,$action_name,$type_document){
		if (is_object($type_document)){
			/** @var DocumentType $type_document */
			$action = $type_document->getAction();
		} else {
			$action =  $this->getAction($type_document);
		}
		$action_rule = $action->getActionRule($action_name);

		foreach($action_rule as $ruleName => $ruleValue){

			if ( ! $this->verifRule($id_e,$id_u,$id_d,$type_document,$ruleName,$ruleValue) ){
				if ($ruleName == "last-action"){
					$last_action = $this->documentActionEntite->getLastAction($id_e,$id_d);
					$this->lastBadRule = "Le dernier état du document ($last_action) ne permet pas de déclencher cette action";	
				} else {
					$this->lastBadRule = "$ruleName n'est pas vérifiée";
				}
				return false;
			}

		}

		return true;
	}
	/**
	 * @param string $type_document
	 * @return Action
	 */
	private function getAction($type_document){
		return $this->documentTypeFactory->getFluxDocumentType($type_document)->getAction();
	}
	
	private function verifRule($id_e,$id_u,$id_d,$type_document,$ruleName,$ruleValue){
		 
		if (!strncmp($ruleName, 'and', 3)){				
			foreach($ruleValue as $ruleName => $ruleElement){
				if (! $this->verifRule($id_e,$id_u,$id_d,$type_document,$ruleName,$ruleElement)){
					return false;
				}
			}
			return true;
		}
		
		if (!strncmp($ruleName, 'or', 2)){				
			foreach($ruleValue as $ruleName => $ruleElement){
				if ($this->verifRule($id_e,$id_u,$id_d,$type_document,$ruleName,$ruleElement)){					
					return true;
				}
			}
			return false;
		}
		if (!strncmp($ruleName, 'no_', 3)){
			foreach($ruleValue as $ruleName => $ruleElement){
				if ($this->verifRule($id_e,$id_u,$id_d,$type_document,$ruleName,$ruleElement)){
					return false;
				}
			}
			return true;
		}
		if (is_array($ruleValue) && ! in_array($ruleName,array('collectivite-properties','herited-properties','content','properties'))){
			foreach($ruleValue as $ruleElement){
				if ($this->verifRule($id_e,$id_u,$id_d,$type_document,$ruleName,$ruleElement)){					
					return true;
				}
			}
			return false;
		}
		
		switch($ruleName){			
			case 'no-last-action' : return $this->verifLastAction($id_e,$id_d,false); break;
			case 'last-action' : return $this->verifLastAction($id_e,$id_d,$ruleValue); break;
			case 'has-action' : return ! $this->verifNoAction($id_e,$id_d,$ruleValue); break;
			case 'no-action':  return $this->verifNoAction($id_e,$id_d,$ruleValue); break;
			case 'role_id_e' : return $this->verifRoleEntite($id_e,$id_d,$ruleValue); break;
			case 'droit_id_u' : return $this->verifDroitUtilisateur($id_e,$id_u,$ruleValue); break;
			case 'content' : return $this->verifContent($id_d,$type_document,$ruleValue); break;
			case 'type_id_e': return $this->veriTypeEntite($id_e,$ruleValue); break;
			case 'document_is_valide' : return $this->verifDocumentIsValide($id_d,$type_document); break;
			case 'automatique': return false;
		}
		throw new Exception("Règle d'action inconnue : $ruleName" );
	} 
	
	private function verifLastAction($id_e,$id_d,$value){				
		return $value == $this->documentActionEntite->getLastAction($id_e,$id_d);
	}
	
	private function verifNoAction($id_e,$id_d,$value){			
		$lesActions =  $this->documentActionEntite->getAction($id_e,$id_d);
		foreach($lesActions as $action){
			if ($action['action'] == $value){
				return false;
			}
		}
		return true;
	}
	
	private function verifRoleEntite($id_e,$id_d,$value){
		return $this->documentEntite->hasRole($id_d,$id_e,$value);
	}
	
	private function verifDroitUtilisateur($id_e,$id_u,$value){
		if ($id_u===0){
			return true;
		}
		return $this->roleUtilisateur->hasDroit($id_u,$value,$id_e);
	}
	
	private function verifContent($id_d,$type,$value){		
		foreach($value as $fieldName => $fieldValue){
			if (! $this->verifField($id_d,$type,$fieldName,$fieldValue)){
				return false;
			}
		}
		return true;
	}
	
	private function verifDocumentIsValide($id_d,$type){
		return $this->donneesFormulaireFactory->get($id_d,$type)->isValidable();
	}
	
	private function verifField($id_d,$type,$fieldName,$fieldValue){
		return $this->donneesFormulaireFactory->get($id_d,$type)->get($fieldName) == $fieldValue;
	}
	
	private function veriTypeEntite($id_e,$type){
		$info = $this->entiteSQL->getInfo($id_e);
		return ($info["type"] == $type);
	}
}