<?php

class ConnecteurFrequence {

	public $id_cf;
	public $type_connecteur;
	public $famille_connecteur;
	public $id_connecteur;
	public $id_ce;
	public $action_type;
	public $type_document;
	public $action;
	public $expression;

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

}