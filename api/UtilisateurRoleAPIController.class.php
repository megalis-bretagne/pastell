<?php

class UtilisateurRoleAPIController extends BaseAPIController {

	const ALL_ROLES = "ALL_ROLES";

	private $utilisateur;

	private $roleSQL;

	private $entiteSQL;


	public function __construct(
		Utilisateur $utilisateur,
		RoleSQL $roleSQL,
		EntiteSQL $entiteSQL
	) {
		$this->utilisateur = $utilisateur;
		$this->roleSQL = $roleSQL;
		$this->entiteSQL = $entiteSQL;
	}

	private function verifExists($id_u){
		$infoUtilisateur = $this->utilisateur->getInfo($id_u);
		if (!$infoUtilisateur) {
			throw new NotFoundException("L'utilisateur n'existe pas : {id_u=$id_u}");
		}
		return $infoUtilisateur;
	}

	private function verifRoleExists($role){
		if (!$this->roleSQL->getInfo($role)) {
			throw new NotFoundException("Le role spécifié n'existe pas {role=$role}");
		}
	}

	public function get() {
		$id_u = $this->getFromQueryArgs(0);
		$id_e = $this->getFromRequest('id_e',0);
		$this->checkDroit($id_e, "utilisateur:lecture");

		$this->verifExists($id_u);

		$roleUtil = $this->getRoleUtilisateur()->getRole($id_u);
		// Construction du tableau de retour
		$result=array();
		foreach ($roleUtil as $id_u_role => $roleU) {
			$result[$id_u_role] = array('id_u' => $roleU['id_u'], 'role' => $roleU['role'], 'id_e' => $roleU['id_e']);
		}

		return $result;
	}

	public function post() {
		$id_u = $this->getFromQueryArgs(0);
		$role = $this->getFromRequest('role');
		$id_e = $this->getFromRequest('id_e');
		return $this->addRoleUtilisateur($id_u,$role,$id_e);
	}

	public function delete() {
		$id_u = $this->getFromQueryArgs(0);
		$role = $this->getFromRequest('role');
		$id_e = $this->getFromRequest('id_e');
		return $this->deleteRoleUtilisateur($id_u,$role,$id_e);

	}

	private function addRoleUtilisateur($id_u,$role,$id_e){
		$this->checkDroit($id_e, "utilisateur:edition");
		$this->verifExists($id_u);
		$this->verifRoleExists($role);

		if(!$this->getRoleUtilisateur()->hasRole($id_u,$role,$id_e)) {
			$this->getRoleUtilisateur()->addRole($id_u,$role,$id_e);
		}

		$result['result'] = self::RESULT_OK;
		return $result;
	}

	private function deleteRoleUtilisateur($id_u,$role,$id_e){
		$this->checkDroit($id_e, "utilisateur:edition");
		$this->verifExists($id_u);

		if($role === self::ALL_ROLES) {
			$this->getRoleUtilisateur()->removeAllRolesEntite($id_u,$id_e);
		}  else {
			$this->verifRoleExists($role);
			$this->getRoleUtilisateur()->removeRole($id_u,$role,$id_e);
		}

		$result['result'] = self::RESULT_OK;
		return $result;
	}

	public function compatV1Edition() {
		$function = $this->getFromQueryArgs(2);
		if ($function == 'add'){
			return $this->addSeveralAction();
		} else {
			return $this->deleteSeveralAction();
		}
	}


	public function addSeveralAction() {
		$data = $this->getRequest();
		$infoUtilisateurExistant = $this->utilisateur->getUserFromData($data);
		$id_u = $infoUtilisateurExistant['id_u'];

		$id_e = $this->getFromRequest('id_e',0);
		if ($id_e) {
			$infoEntiteExistante = $this->entiteSQL->getEntiteFromData($data);
			$id_e = $infoEntiteExistante['id_e'];
		}

		$roles = $this->getFromRequest('role');
		if (! $roles){
			return array();
		}

		$deleteRoles = $this->getFromRequest('deleteRoles',false);
		if($deleteRoles) {
			$this->deleteRoleUtilisateur($id_u, self::ALL_ROLES , $id_e);
		}

		if(is_array($roles)) {
			$result = array();
			foreach($roles as $role) {
				$result[] = $this->addRoleUtilisateur($id_u, $role, $id_e);
			}
		}  else {
			$result = $this->addRoleUtilisateur($id_u, $roles, $id_e);
		}
		return $result;

	}

	public function deleteSeveralAction() {
		$data = $this->getRequest();

		$infoUtilisateurExistant = $this->utilisateur->getUserFromData($data);
		$infoEntiteExistante = $this->entiteSQL->getEntiteFromData($data);

		$roles = $this->getFromRequest('role');

		if (! $roles){
			return array();
		}

		$id_e = $infoEntiteExistante['id_e'];
		$id_u = $infoUtilisateurExistant['id_u'];

		if(is_array($roles)) {
			$result = array();
			foreach($roles as $role) {
				$result[] = $this->deleteRoleUtilisateur($id_u, $role, $id_e);
			}
		}  else {
			$result = $this->deleteRoleUtilisateur($id_u, $roles, $id_e);
		}
		return $result;
	}


	
}