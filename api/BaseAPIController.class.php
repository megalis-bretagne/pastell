<?php

abstract class BaseAPIController {

	const RESULT_OK = "ok";

	private $id_u;
	private $request = array();

	private $caller_type;


	/** @var RoleUtilisateur */
	private $roleUtilisateur;

	/** @var  FileUploader */
	private $fileUploader;

	/* TODO temporaire */
	private $method_name;
	public function setMethodName($method_name){
		$this->method_name = $method_name;
	}

	public function get(){
		if (! method_exists($this,$this->method_name)){
			throw new Exception("Method not allowed");
		}
		return $this->{$this->method_name}();
	}

	public function post(){
		if (! method_exists($this,$this->method_name)){
			throw new Exception("Method not allowed");
		}
		return $this->{$this->method_name}();
	}
	/* FIN temporaire */

	public function setCallerType($caller_type){
		$this->caller_type = $caller_type;
	}

	public function getCallerType(){
		return $this->caller_type;
	}

	public function setFileUploader(FileUploader $fileUploader){
		$this->fileUploader = $fileUploader;
	}

	public function getFileUploader(){
		return $this->fileUploader;
	}

	public function setRoleUtilisateur(RoleUtilisateur $roleUtilisateur) {
		$this->roleUtilisateur = $roleUtilisateur;
	}

	public function getRoleUtilisateur(){
		return $this->roleUtilisateur;
	}
	
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

	public function getRequest(){
		return $this->request;
	}


	protected function verifDroit($id_e,$droit){
		if  (! $this->getRoleUtilisateur()->hasDroit($this->id_u,$droit,$id_e)){
			throw new Exception("Acces interdit id_e=$id_e, droit=$droit,id_u={$this->id_u}");
		}
	}

	protected function hasOneDroit($droit){
		if (!$this->getRoleUtilisateur()->hasOneDroit($this->getUtilisateurId(), $droit)) {
			throw new Exception("Acces interdit type=$droit,id_u=$this->id_u");
		}
	}



}