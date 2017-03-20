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

	private $query_args;

	private $hasAllDroit;

	public function setQueryArgs(array $query_args){
		$this->query_args = $query_args;
	}

	public function getFromQueryArgs($place_number){
		if (empty($this->query_args[$place_number])){
			return false;
		}
		return $this->query_args[$place_number];
	}

	public function setAllDroit($hasAllDroit = false){
		$this->hasAllDroit = $hasAllDroit;
	}

	/* TODO temporaire */
	public function getMethodName(){
		return $this->query_args[0]."Action";
;	}
	public function get(){
		if (! method_exists($this,$this->getMethodName())){
			throw new Exception("Method not allowed");
		}
		return $this->{$this->getMethodName()}();
	}

	public function post(){
		if (! method_exists($this,$this->getMethodName())){
			throw new Exception("Method not allowed");
		}
		return $this->{$this->getMethodName()}();
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
		if ($this->hasAllDroit){
			return true;
		}
		if  (! $this->getRoleUtilisateur()->hasDroit($this->id_u,$droit,$id_e)){
			throw new Exception("Acces interdit id_e=$id_e, droit=$droit,id_u={$this->id_u}");
		}
		return true;
	}

	protected function checkOneDroit($droit){
		if (!$this->hasOneDroit($droit)) {
			throw new Exception("Acces interdit type=$droit,id_u=$this->id_u");
		}
		return true;
	}

	public function hasOneDroit($droit){
		if ($this->hasAllDroit){
			return true;
		}
		return $this->getRoleUtilisateur()->hasOneDroit($this->getUtilisateurId(), $droit);
	}


}