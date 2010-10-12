<?php
class Action {
	
	const ACTION_DISPLAY_NAME = "name";
	const ACTION_RULE = "rule";
	const ACTION_SCRIPT = "action-script";
	
	const CREATION = "creation";
	const MODIFICATION = "modification";
	
	private $tabAction;
	
	public function __construct(array $tabAction){
		$this->tabAction = $tabAction;
	}
	
	public function getAll(){
		return array_keys($this->tabAction);
	}
	
	public function getActionName($action_internal_name){
		$tabAction = $this->getActionArray($action_internal_name);
		if (! isset($tabAction[self::ACTION_DISPLAY_NAME])){
			return $action_internal_name;
		}
		return $tabAction[self::ACTION_DISPLAY_NAME];
	}
	
	private function getActionArray($action_internal_name){
		if (! isset($this->tabAction[$action_internal_name])){
			throw new Exception("L'action $action_internal_name est inconnue. Veuillez contacter votre administrateur Pastell");
		}
		return $this->tabAction[$action_internal_name];
	}
	
	public function getActionRule($action_internal_name){
		$tabAction = $this->getActionArray($action_internal_name);
		if ( ! isset($tabAction[self::ACTION_RULE])){
			return array();
		}
		return $tabAction[self::ACTION_RULE];
	}
	
	public function getProperties($action,$properties){
		$tabAction = $this->getActionArray($action);
		if (! isset ($tabAction[$properties])){
			return false;
		}
		return $tabAction[$properties];
	}
	
	public function getActionScript($action_internal_name){
		$tabAction = $this->getActionArray($action_internal_name);
		if (! isset($tabAction[self::ACTION_SCRIPT])){
			throw new Exception("L'action $action_internal_name n'est associ� � aucun script");
		}
		return $tabAction[self::ACTION_SCRIPT];
	}
}