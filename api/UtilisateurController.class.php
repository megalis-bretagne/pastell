<?php


class UtilisateurController extends BaseAPIController {

	//FIXME Inverser l'utilisation du controller
	private $utilisateurControler;

	private $utilisateur;

	private $utilisateurListe;

	private $roleSQL;

	private $entiteSQL;

	public function __construct(
		UtilisateurControler $utilisateurControler,
		Utilisateur $utilisateur,
		UtilisateurListe $utilisateurListe,
		RoleSQL $roleSQL,
		EntiteSQL $entiteSQL
	) {
		$this->utilisateurControler = $utilisateurControler;
		$this->utilisateur = $utilisateur;
		$this->utilisateurListe = $utilisateurListe;
		$this->roleSQL = $roleSQL;
		$this->entiteSQL = $entiteSQL;
	}

	/**
	 * @api {get} /create-utilisateur.php /Utilisateur/create
	 * @apiDescription Créer un utilisateur
	 * @apiGroup Utilisateur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {string} login requis Login de l'utilisateur
	 * @apiParam {string} password requis Mot de passe de l'utilisateur
	 * @apiParam {string} prenom requis Prénom de l'utilisateur
	 * @apiParam {string} nom requis Nom de l'utilisateur
	 * @apiParam {int} id_e Identifiant de la collectivité de base de l'utilisateur (défaut 0)
	 * @apiParam {string} email Email de l'utilisateur
	 *
	 * @apiSuccess {int} id_u L'identifiant de l'utilisateur créé
	 */
	public function createAction() {
		//FIXME Inverser l'utilisation du controller

		$data = $this->getRequest();

		if (isset($data['id_e'])) {
			$id_e = $data['id_e'];
		} else {
			$id_e = 0;
		}

		// Vérification des droits.
		$this->verifDroit($id_e, "utilisateur:edition");

		$certificat_content = $this->getFileUploader()->getFileContent('certificat');

		$id_u_cree = $this->utilisateurControler->editionUtilisateur(
			$id_e,
			null,
			$data['email'],
			$data['login'],
			$data['password'],
			$data['password'],
			$data['nom'],
			$data['prenom'],
			$certificat_content
		);
		$info['id_u']= $id_u_cree;
		return $info;
	}


	/**
	 * @api {get} /modif-utilisateur.php /Utilisateur/edit
	 * @apiDescription Permet la modification d'un utilisateur soit par son identifiant, soit par son login.
						Dans le cas où ces deux paramètres sont renseignés, seul l'identifiant sera pris en compte.
	 * @apiGroup Utilisateur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string} login  Login de l'utilisateur
	 * @apiParam {string} password Mot de passe de l'utilisateur
	 * @apiParam {string} prenom Prénom de l'utilisateur
	 * @apiParam {string} nom Nom de l'utilisateur
	 * @apiParam {int} id_e Identifiant de la collectivité de base de l'utilisateur (défaut 0)
	 * @apiParam {string} email Email de l'utilisateur
	 * @apiParam {boolean} Flag permettant la création de l'utilisateur si aucun autre utilisateur ne porte le même nom
	 * 						(faux par défaut)
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function editAction() {

		$fileUploader = $this->getFileUploader();
		$data = $this->getRequest();


		$utilisateur = $this->utilisateur;

		// Possibilité de créer un utilisateur si celui ci n'existe pas
		$createUtilisateur = isset($data['create']) ? $data['create'] : FALSE;

		// Recherche de l'utilisateur par son identifiant
		if(isset($data['id_u'])) {
			$id_u_a_modifier = $data['id_u'];
			// Chargement de l'utilisateur en base de données
			$infoUtilisateurExistant = $utilisateur->getInfo($id_u_a_modifier);
			if (!$infoUtilisateurExistant) {
				throw new Exception("L'identifiant de l'utilisateur n'existe pas : {id_u=$id_u_a_modifier}");
			}
		}
		// Recherche de l'utilisateur par son login
		elseif(isset($data['login'])) {
			$login = $data['login'];
			// Chargement de l'utilisateur en base de données
			$infoUtilisateurExistant = $utilisateur->getInfoByLogin($login);

			// Si l'utilisateur n'existe pas et que l'on n'a pas spécifié vouloir le créer
			if (!$infoUtilisateurExistant && !$createUtilisateur) {
				throw new Exception("Le login de l'utilisateur n'existe pas : {login=$login}");
			}
			$id_u_a_modifier = $infoUtilisateurExistant['id_u'];
		}
		// Impossible de rechercher l'utilisateur sans son identifiant ni son login
		else {
			throw new Exception("Aucun paramètre permettant la recherche de l'utilisateur n'a été renseigné");
		}

		// Si l'utilisateur n'existe pas et qu'on a spécifié vouloir le créer
		if(!$infoUtilisateurExistant && $createUtilisateur) {
			return $this->createAction();
		}
		$id_e = $infoUtilisateurExistant["id_e"];

		// Vérification des droits.
		$this->verifDroit($id_e, "utilisateur:edition");


		// Modification de l'utilisateur chargé avec les infos passées par l'API
		foreach ($data as $key => $newValeur) {
			if (array_key_exists($key, $infoUtilisateurExistant)) {
				$infoUtilisateurExistant[$key] = $newValeur;
			}
		}

		$login = $infoUtilisateurExistant['login'];
		$password = $infoUtilisateurExistant['password'];
		$password2 = $infoUtilisateurExistant['password'];
		$nom = $infoUtilisateurExistant['nom'];
		$prenom = $infoUtilisateurExistant['prenom'];
		$email = $infoUtilisateurExistant['email'];


		$certificat_content = $fileUploader->getFileContent('certificat');


		// Appel du service métier pour enregistrer la modification de l'utilisateur
		$this->utilisateurControler->editionUtilisateur($id_e, $id_u_a_modifier, $email, $login, $password, $password2, $nom, $prenom, $certificat_content);

		// Si le certificat n'est pas passé, il faut le supprimer de l'utilisateur
		// Faut-il garder ce comportement ou faire des webservices dédiés à la gestion des certificats (au moins la suppression) ?
		if (!$certificat_content) {
			$utilisateur->removeCertificat($id_u_a_modifier);
		}

		$result['result'] = self::RESULT_OK;
		return $result;
	}


	/** TODO documentation ! */
	public function detailAction() {

		$id_u = $this->getFromRequest('id_u');

		// Chargement de l'utilisateur en base de données
		$infoUtilisateur = $this->utilisateur->getInfo($id_u);

		// Chargement de l'utilisateur en base de données
		if (!$infoUtilisateur) {
			throw new Exception("L'utilisateur n'existe pas : {id_u=$id_u}");
		}

		// Vérification des droits.
		$this->verifDroit($infoUtilisateur['id_e'], "utilisateur:lecture");


		// Création d'un nouveau tableau pour ne retourner que les valeurs retenues
		$result = array();
		$result['id_u'] = $infoUtilisateur['id_u'];
		$result['login'] = $infoUtilisateur['login'];
		$result['nom'] = $infoUtilisateur['nom'];
		$result['prenom'] = $infoUtilisateur['prenom'];
		$result['email'] = $infoUtilisateur['email'];
		$result['certificat'] = $infoUtilisateur['certificat'];
		$result['id_e'] = $infoUtilisateur['id_e'];

		return $result;
	}


	/**
	 * @api {get} /delete-utilisateur.php /Utilisateur/delete
	 * @apiDescription Permet la suppression d'un utilisateur soit par son identifiant, soit par son login.
	 * 					Dans le cas où ces deux paramètres sont renseignés, seul l'identifiant sera pris en compte.
	 * @apiGroup Utilisateur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string} login  Login de l'utilisateur
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function deleteAction() {

		$data = $this->getRequest();

		// Chargement de l'utilisateur
		$utilisateurModel = $this->utilisateur;

		// Recherche de l'utilisateur par son identifiant
		if(isset($data['id_u'])) {
			$id_u = $data['id_u'];

			$infoUtilisateur = $utilisateurModel->getInfo($id_u);

			if (!$infoUtilisateur) {
				throw new Exception("L'identifiant de l'utilisateur n'existe pas : {id_u=$id_u}");
			}
		}
		// Recherche de l'utilisateur par son login
		elseif(isset($data['login'])) {
			$login = $data['login'];

			$infoUtilisateur = $utilisateurModel->getInfoByLogin($login);

			if (!$infoUtilisateur) {
				throw new Exception("Le login de l'utilisateur n'existe pas : {login=$login}");
			}
			$id_u = $infoUtilisateur['id_u'];
		}
		// Aucun paramètre renseigné
		else {
			throw new Exception("Aucun paramètre n'a été renseigné");
		}

		// Vérification des droits.
		$this->verifDroit($infoUtilisateur['id_e'], "utilisateur:edition");

		// Suppression des données
		$this->getRoleUtilisateur()->removeAllRole($id_u);
		$utilisateurModel->desinscription($id_u);

		$result['result'] = self::RESULT_OK;
		return $result;
	}


	//TODO Documentation !
	public function listAction() {
		$id_e = $this->getFromRequest('id_e',0);

		$this->verifDroit($id_e, "utilisateur:lecture");

		$listUtilisateur = $this->utilisateurListe->getAllUtilisateurSimple($id_e);
		$result=array();
		if ($listUtilisateur) {
			// Création d'un nouveau tableau pour ne retourner que les valeurs retenues
			foreach($listUtilisateur as $id_u => $utilisateur) {
				$result[$id_u] = array('id_u' => $utilisateur['id_u'], 'login' => $utilisateur['login'], 'email' => $utilisateur['email']);
			}
		}
		return $result;
	}

	/**
	 * @api {get} /add-role-utilisateur.php /Utilisateur/addRole
	 * @apiDescription Permet l'ajout d'un role d'un utilisateur pour une collectivité donnée.
	 * @apiGroup Utilisateur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string} role  Role à ajouter
	 * @apiParam {int} id_e  Identifiant de l'entité
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function addRoleAction() {
		$id_u = $this->getFromRequest('id_u');
		$role = $this->getFromRequest('role');
		$id_e = $this->getFromRequest('id_e');
		return $this->addRoleUtilisateur($id_u,$role,$id_e);

	}

	private function addRoleUtilisateur($id_u,$role,$id_e){
		// Vérification des droits.
		$this->verifDroit($id_e, "utilisateur:edition");

		if(!$this->utilisateur->getInfo($id_u)) {
			throw new Exception("L'utilisateur spécifié n'existe pas {id_u=$id_u}");
		}

		if (!$this->roleSQL->getInfo($role)) {
			throw new Exception("Le role spécifié n'existe pas {role=$role}");
		}
		if(!$this->getRoleUtilisateur()->hasRole($id_u,$role,$id_e)) {
			$this->getRoleUtilisateur()->addRole($id_u,$role,$id_e);
		}

		$result['result'] = self::RESULT_OK;
		return $result;
	}


	/**
	 * @api {get} /delete-role-utilisateur.php /Utilisateur/deleteRole
	 * @apiDescription Permet la suppression d'un role d'un utilisateur pour une collectivité donnée.
	 * @apiGroup Utilisateur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string} role  Role à supprimer.
	 * 				Pour supprimer l'ensemble des roles de l'utilisateur pour cette collectivité, utiliser "ALL_ROLES"
	 * @apiParam {int} id_e  Identifiant de l'entité
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function deleteRoleAction() {
		$id_u = $this->getFromRequest('id_u');
		$role = $this->getFromRequest('role');
		$id_e = $this->getFromRequest('id_e');
		return $this->deleteRoleUtilisateur($id_u,$role,$id_e);

	}


	private function deleteRoleUtilisateur($id_u,$role,$id_e){
		$allRoles = "ALL_ROLES";
		// Vérification des droits
		$this->verifDroit($id_e, "utilisateur:edition");

		if(!$this->utilisateur->getInfo($id_u)) {
			throw new Exception("L'utilisateur spécifié n'existe pas {id_u=$id_u}");
		}
		//Supprime tous les roles de l'utilisateur pour cette entité
		if($role === $allRoles) {
			$this->getRoleUtilisateur()->removeAllRolesEntite($id_u,$id_e);
		}
		else {
			if (!$this->roleSQL->getInfo($role)) {
				throw new Exception("Le role spécifié n'existe pas {role=$role}");
			}

			$this->getRoleUtilisateur()->removeRole($id_u,$role,$id_e);
		}

		$result['result'] = self::RESULT_OK;
		return $result;

	}


	/**
	 * @api {get} /add-several-roles-utilisateur.php /Utilisateur/addSeveralRole
	 * @apiDescription Permet l'ajout d'un ou plusieurs roles d'un utilisateur pour une collectivité donnée.
						L'utilisateur peut être choisi par son identifiant ou son login. Si les deux paramètres sont renseignés, son identifiant sera utilisé.
						La collectivité peut être choisie par son identifiant ou sa dénomination. Si les deux paramètres sont renseignés, son identifiant sera utilisé.
						Dans le cas où la dénomination est choisie, si deux entités portent le même nom, aucune action ne sera effectuée.

	 * @apiGroup Utilisateur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string[]} role  Les rôles à ajouter. Possibilité de n'envoyer qu'un simple role également.
	 * @apiParam {int} id_e  Identifiant de l'entité
	 * @apiParam {boolean} id_e  deleteRoles Flag permettant la suppression
	 * 						de tous les roles de l'utilisateur avant de lui attribuer les nouveaux
	 * 						(défaut : FALSE)
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function addSeveralRoleAction() {
		$data = $this->getRequest();
		$infoUtilisateurExistant = $this->getUserFromData($data);
		$infoEntiteExistante = $this->getEntiteFromData($data);

		// Possibilité de supprimer les anciens roles avant d'ajouter les nouveaux
		$deleteRoles = isset($data['deleteRoles']) ? $data['deleteRoles'] : FALSE;

		if(isset($data['role'])) {
			$roles = $data['role'];
			$id_e = $infoEntiteExistante['id_e'];
			$id_u = $infoUtilisateurExistant['id_u'];

			//Suppression des anciens roles
			if($deleteRoles) {
				$this->deleteRoleUtilisateur($id_u, 'ALL_ROLES', $id_e);
			}

			if(is_array($roles)) {
				$result = array();
				foreach($roles as $role) {
					$result[] = $this->addRoleUtilisateur($id_u, $role, $id_e);
				}
			}
			else {
				$result = $this->addRoleUtilisateur($id_u, $roles, $id_e);
			}
			return $result;
		}
		return false;
	}


	private function getUserFromData(&$data) {
		$utilisateur = $this->utilisateur;
		//Recherche de l'utilisateur par son identifiant
		if(isset($data['id_u'])) {
			$id_u = $data['id_u'];
			$infoUtilisateurExistant = $utilisateur->getInfo($id_u);
			if (!$infoUtilisateurExistant) {
				throw new Exception("L'identifiant de l'utilisateur n'existe pas : {id_u=$id_u}");
			}
		}
		// Recherche de l'utilisateur par son login
		elseif(isset($data['login'])) {
			$login = $data['login'];
			$infoUtilisateurExistant = $utilisateur->getInfoByLogin($login);

			if (!$infoUtilisateurExistant) {
				throw new Exception("Le login de l'utilisateur n'existe pas : {login=$login}");
			}
		}
		// Impossible de rechercher l'utilisateur sans son identifiant ni son login
		else {
			throw new Exception("Aucun paramètre permettant la recherche de l'utilisateur n'a été renseigné");
		}

		return $infoUtilisateurExistant;
	}

	private function getEntiteFromData(&$data) {
		$entite = $this->entiteSQL;
		//Recherche de l'entite par son identifiant
		if(isset($data['id_e'])) {
			$id_e = $data['id_e'];
			$infoEntiteExistante = $entite->getInfo($id_e);
			if (!$infoEntiteExistante) {
				throw new Exception("L'identifiant de l'entite n'existe pas : {id_e=$id_e}");
			}
		}
		// Recherche de l'entité par sa dénomination
		elseif(isset($data['denomination'])) {
			$denomination = $data['denomination'];
			$numberOfEntite = $entite->getNumberOfEntiteWithName($denomination);

			if($numberOfEntite == 0) {
				throw new Exception("La dénomination de l'entité n'existe pas : {denomination=$denomination}");
			}
			elseif($numberOfEntite > 1) {
				throw new Exception("Plusieurs entités portent le même nom, préférez utiliser son identifiant");
			}
			//Si une seule entité porte ce nom
			else {
				$infoEntiteExistante = $entite->getInfoByDenomination($denomination);
			}
		}
		// Impossible de rechercher l'entité sans son identifiant ni sa dénomination
		else {
			throw new Exception("Aucun paramètre permettant la recherche de l'entité n'a été renseigné");
		}

		return $infoEntiteExistante;
	}

	/**
	 * @api {get} /delete-several-roles-utilisateur.php /Utilisateur/deleteSeveralRole
	 * @apiDescription Permet la suppression d'un ou plusieurs roles d'un utilisateur pour une collectivité donnée.
	 * 					L'utilisateur peut être choisi par son identifiant ou son login. Si les deux paramètres sont renseignés, son identifiant sera utilisé.
	 * 					La collectivité peut être choisie par son identifiant ou sa dénomination. Si les deux paramètres sont renseignés, son identifiant sera utilisé.
	 * 					Dans le cas où la dénomination est choisie, si deux entités portent le même nom, aucune action ne sera effectuée.
	 * @apiGroup Utilisateur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string[]} role  Tableau de roles à supprimer. Possibilité de n'envoyer qu'un simple role également.
	 * @apiParam {int} id_e  Identifiant de l'entité
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 */
	public function deleteSeveralRoleAction() {
		$data = $this->getRequest();

		$infoUtilisateurExistant = $this->getUserFromData($data);
		$infoEntiteExistante = $this->getEntiteFromData($data);

		if(isset($data['role'])) {
			$roles = $data['role'];
			$id_e = $infoEntiteExistante['id_e'];
			$id_u = $infoUtilisateurExistant['id_u'];

			if(is_array($roles)) {
				$result = array();
				foreach($roles as $role) {
					$result[] = $this->deleteRoleUtilisateur($id_u, $role, $id_e);
				}
			}
			else {
				$result = $this->deleteRoleUtilisateur($id_u, $roles, $id_e);
			}
			return $result;
		}
		return false;
	}


	//TODO Documentation !
	public function listRoleAction() {
		$id_u = $this->getFromRequest('id_u');
		$id_e = $this->getFromRequest('id_e',0);

		$this->verifDroit($id_e, "utilisateur:lecture");

		if(!$this->utilisateur->getInfo($id_u)) {
			throw new Exception("L'utilisateur spécifié n'existe pas {id_u=$id_u}");
		}

		$roleUtil = $this->getRoleUtilisateur()->getRole($id_u);
		// Construction du tableau de retour
		$result=array();
		foreach ($roleUtil as $id_u_role => $roleU) {
			$result[$id_u_role] = array('id_u' => $roleU['id_u'], 'role' => $roleU['role'], 'id_e' => $roleU['id_e']);
		}

		return $result;
	}

}