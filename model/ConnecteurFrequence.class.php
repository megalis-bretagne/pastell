<?php

class ConnecteurFrequence {

	const TYPE_GLOBAL = 'global';
	const TYPE_ENTITE = 'entite';

	const TYPE_ACTION_DOCUMENT = 'document';
	const TYPE_ACTION_CONNECTEUR = 'connecteur';

	public $id_cf;
	public $type_connecteur;
	public $famille_connecteur;
	public $id_connecteur;
	public $id_ce;
	public $action_type;
	public $type_document;
	public $action;
	public $expression;
	public $id_verrou;

	public function __construct(array $input = array()) {
		foreach(get_object_vars($this) as $key => $value){
			if (isset($input[$key])) {
				$this->$key = $input[$key]?:'';
			} else {
				$this->$key = '';
			}
		}
		if (isset($input['id_cf'])) {
			$input['id_cf'] = intval($input['id_cf']);
		}
	}

	public function getArray(){
		return get_object_vars($this);
	}

	public function getConnecteurSelector(){
		if ($this->type_connecteur == ''){
			return 'Tous les connecteurs';
		}
		if ($this->type_connecteur == self::TYPE_GLOBAL){
			$result = "(Global)";
		} else {
			$result = "(Entité)";
		}

		if ($this->famille_connecteur == ''){
			return $result." Tous les connecteurs";
		}

		$result .= " ".$this->famille_connecteur;

		if ($this->id_connecteur == ''){
			return $result;
		}

		return $result.":".$this->id_connecteur;
	}

	public function getActionSelector(){
		if ($this->action_type == ''){
			return "Toutes les actions";
		}
		$result = "";
		if ($this->action_type == self::TYPE_ACTION_CONNECTEUR){
			$result .= "(Connecteur) ";
		} else {
			$result .= "(Document) ";
			if ($this->type_document == ''){
				return $result."Tous les types de documents";
			}
			$result .= "{$this->type_document}: ";
		}
		if ($this->action == ''){
			$result .= "toutes les actions";
		} else {
			$result .= $this->action;
		}
		return $result;
	}
}