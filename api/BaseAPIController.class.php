<?php

abstract class BaseAPIController {

	private $id_u;
	private $request = array();

	/** @var RoleUtilisateur */
	private $roleUtilisateur;

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

	protected function verifDroit($id_e,$droit){
		if  (! $this->getRoleUtilisateur()->hasDroit($this->id_u,$droit,$id_e)){
			throw new Exception("Acces interdit id_e=$id_e, droit=$droit,id_u={$this->id_u}");
		}
	}

}