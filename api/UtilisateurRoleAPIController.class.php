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
			throw new Exception("L'utilisateur n'existe pas : {id_u=$id_u}");
		}
		return $infoUtilisateur;
	}

	private function verifRoleExists($role){
		if (!$this->roleSQL->getInfo($role)) {
			throw new Exception("Le role spécifié n'existe pas {role=$role}");
		}
	}

	/**
	 * @api {get} /UtilisateurRole/add /UtilisateurRole/add
	 * @apiDescription Permet l'ajout d'un role d'un utilisateur pour une entité donnée. (was: /add-role-utilisateur.php)
	 * @apiGroup UtilisateurRole
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string} role  Role à ajouter
	 * @apiParam {int} id_e  Identifiant de l'entité
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function addAction() {
		$id_u = $this->getFromRequest('id_u');
		$role = $this->getFromRequest('role');
		$id_e = $this->getFromRequest('id_e');
		return $this->addRoleUtilisateur($id_u,$role,$id_e);

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


	/**
	 * @api {get} /UtilisateurRole/delete /UtilisateurRole/delete
	 * @apiDescription Permet la suppression d'un role d'un utilisateur pour une entité donnée. (was: /delete-role-utilisateur.php)
	 * @apiGroup UtilisateurRole
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string} role  Role à supprimer.
	 * 				Pour supprimer l'ensemble des roles de l'utilisateur pour cette collectivité, utiliser "ALL_ROLES"
	 * @apiParam {int} id_e  Identifiant de l'entité
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function deleteAction() {
		$id_u = $this->getFromRequest('id_u');
		$role = $this->getFromRequest('role');
		$id_e = $this->getFromRequest('id_e');
		return $this->deleteRoleUtilisateur($id_u,$role,$id_e);

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


	/**
	 * @api {get}  /UtilisateurRole/addSeveral /UtilisateurRole/addSeveral
	 * @apiDescription Permet l'ajout d'un ou plusieurs roles d'un utilisateur pour une entité donnée.
	L'utilisateur peut être choisi par son identifiant ou son login. Si les deux paramètres sont renseignés, son identifiant sera utilisé.
	La collectivité peut être choisie par son identifiant ou sa dénomination. Si les deux paramètres sont renseignés, son identifiant sera utilisé.
	Dans le cas où la dénomination est choisie, si deux entités portent le même nom, aucune action ne sera effectuée.
	(was: /add-several-roles-utilisateur.php)
	 * @apiGroup UtilisateurRole
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string[]} role  Les rôles à ajouter. Possibilité de n'envoyer qu'un simple role également.
	 * @apiParam {int} id_e  Identifiant de l'entité
	 * @apiParam {boolean} deleteRoles Flag permettant la suppression
	 * 						de tous les roles de l'utilisateur avant de lui attribuer les nouveaux
	 * 						(défaut : FALSE)
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
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
			return false;
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

	/**
	 * @api {get} /UtilisateurRole/deleteSeveral /UtilisateurRole/deleteSeveral
	 * @apiDescription Permet la suppression d'un ou plusieurs roles d'un utilisateur pour une entité donnée.
	 * 					L'utilisateur peut être choisi par son identifiant ou son login. Si les deux paramètres sont renseignés, son identifiant sera utilisé.
	 * 					La collectivité peut être choisie par son identifiant ou sa dénomination. Si les deux paramètres sont renseignés, son identifiant sera utilisé.
	 * 					Dans le cas où la dénomination est choisie, si deux entités portent le même nom, aucune action ne sera effectuée.
	 * 					(was:  /delete-several-roles-utilisateur.php)
	 * @apiGroup UtilisateurRole
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string[]} role  Tableau de roles à supprimer. Possibilité de n'envoyer qu'un simple role également.
	 * @apiParam {int} id_e  Identifiant de l'entité
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function deleteSeveralAction() {
		$data = $this->getRequest();

		$infoUtilisateurExistant = $this->utilisateur->getUserFromData($data);
		$infoEntiteExistante = $this->entiteSQL->getEntiteFromData($data);

		$roles = $this->getFromRequest('role');

		if (! $roles){
			return false;
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

	/**
	 * @api {get}  /UtilisateurRole/list /UtilisateurRole/list
	 * @apiDescription Liste les rôles de l'utilisateur (was: /list-role-utilisateur.php)
	 * @apiGroup UtilisateurRole
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {int} id_e  Identifiant de l'entité
	 *
	 * @apiSuccess {Object[]} utilisateur_role liste des élements role_utilisateur
	 * @apiSuccess {int} id_u identifiant de l'utilisateur
	 * @apiSuccess {int} id_e identifiant de l'entite
	 * @apiSuccess {int} role role de l'utilisateur sur l'entité
	 */
	public function listAction() {
		$id_u = $this->getFromRequest('id_u');
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
	
}