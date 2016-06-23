<?php

abstract class BaseAPIController {

	private $id_u;
	private $request = array();

	public function setUtilisateurId($id_u){
		$this->id_u = $id_u;
	}

	public function getUtilisateurId(){
		return $this->id_u;
	}

	public function setRequestInfo(array $request){
		$this->request = $request;
	}

	public function getFromRequest($key,$default = false){
		if (! isset($this->request[$key])){
			return $default;
		}
		return $this->request[$key];
	}

}