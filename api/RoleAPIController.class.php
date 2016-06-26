<?php

class RoleAPIController extends BaseAPIController {

	/**
	 * @api {get} /list-roles.php /Role/list
	 * @apiDescription Listes les rôles
	 * @apiGroup Role
	 * @apiVersion 1.0.0
	 * @apiSuccess {Object[]} role liste de rôles
	 * @apiSuccess {string} role.role Identifiant du rôle
	 * @apiSuccess {string} role.libelle Libellé du rôle
	 */
	public function listAction(){
		$this->hasOneDroit("role:lecture");
		return $this->getRoleUtilisateur()->getAllRoles();
	}
}