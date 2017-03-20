<?php

class RoleAPIController extends BaseAPIController {

	public function get(){
		$this->hasOneDroit("role:lecture");
		return $this->getRoleUtilisateur()->getAuthorizedRoleToDelegate($this->getUtilisateurId());
	}
}