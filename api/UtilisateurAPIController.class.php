<?php


class UtilisateurAPIController extends BaseAPIController {

	private $utilisateur;

	private $utilisateurListe;


	private $utilisateurCreator;

	private $roleUtilisateur;

	private $journal;

	public function __construct(
		Utilisateur $utilisateur,
		UtilisateurListe $utilisateurListe,		
		UtilisateurCreator $utilisateurCreator,
		RoleUtilisateur $roleUtilisateur,
		Journal $journal
	) {
		$this->utilisateur = $utilisateur;
		$this->utilisateurListe = $utilisateurListe;
		$this->utilisateurCreator = $utilisateurCreator;
		$this->roleUtilisateur = $roleUtilisateur;
		$this->journal = $journal;
	}


	private function verifExists($id_u){
		$infoUtilisateur = $this->utilisateur->getInfo($id_u);
		if (!$infoUtilisateur) {
			throw new Exception("L'utilisateur n'existe pas : {id_u=$id_u}");
		}
		return $infoUtilisateur;
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

		$id_e = $this->getFromRequest('id_e',0);
		$this->verifDroit($id_e, "utilisateur:edition");

		$info['id_u'] = $this->editionUtilisateur(
			$id_e,
			null,
			$this->getFromRequest('email'),
			$this->getFromRequest('login'),
			$this->getFromRequest('password'),
			$this->getFromRequest('nom'),
			$this->getFromRequest('prenom'),
			$this->getFileUploader()->getFileContent('certificat')
		);
		return $info;
	}

	private function editionUtilisateur($id_e,$id_u,$email,$login,$password,$nom,$prenom,$certificat_content){
		if (! $nom){
			throw new Exception("Le nom est obligatoire");
		}

		if (! $prenom){
			throw new Exception("Le prénom est obligatoire");
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
			throw new Exception("Votre adresse email ne semble pas valide");
		}

		
		if ($certificat_content){
			$certificat = new Certificat($certificat_content);
			if (! $certificat->isValid()){
				throw new Exception("Le certificat ne semble pas être valide");
			}
		}
		$other_id_u =$this->utilisateur->getIdFromLogin($login);
		if ($id_u && $other_id_u && $other_id_u != $id_u){
			throw new Exception("Un utilisateur avec le même login existe déjà.");
		}
		
		if (! $id_u){
			$id_u = $this->utilisateurCreator->create($login,$password,$password,$email);
			if ( ! $id_u){
				throw new Exception($this->utilisateurCreator->getLastError());
			}
		}
		if ( $password ){
			$this->utilisateur->setPassword($id_u,$password);
		}
		$oldInfo = $this->utilisateur->getInfo($id_u);

		if (! empty($certificat)){
			$this->utilisateur->setCertificat($id_u,$certificat);
		}

		$this->utilisateur->validMailAuto($id_u);
		$this->utilisateur->setNomPrenom($id_u,$nom,$prenom);
		$this->utilisateur->setEmail($id_u,$email);
		$this->utilisateur->setLogin($id_u,$login);
		$this->utilisateur->setColBase($id_u,$id_e);

		$allRole = $this->roleUtilisateur->getRole($id_u);
		if (! $allRole ){
			$this->roleUtilisateur->addRole($id_u,RoleUtilisateur::AUCUN_DROIT,$id_e);
		}

		$newInfo = $this->utilisateur->getInfo($id_u);

		$infoToRetrieve = array('email','login','nom','prenom');
		$infoChanged = array();
		foreach($infoToRetrieve as $key){
			if ($oldInfo[$key] != $newInfo[$key]){
				$infoChanged[] = "$key : {$oldInfo[$key]} -> {$newInfo[$key]}";
			}
		}
		$infoChanged  = implode("; ",$infoChanged);

		$this->journal->add(
			Journal::MODIFICATION_UTILISATEUR,
			$id_e,
			0,
			"Edité",
			"Edition de l'utilisateur $login ($id_u) : $infoChanged"
		);

		return $id_u;
	}

	/**
	 * @api {get} /modif-utilisateur.php /Utilisateur/edit
	 * @apiDescription Permet la modification d'un utilisateur soit par son identifiant, soit par son login.
						Dans le cas où ces deux paramètres sont renseignés, seul l'identifiant sera pris en compte.
	 * @apiGroup Utilisateur
	 * @apiVersion 2.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 * @apiParam {string} login  Login de l'utilisateur
	 * @apiParam {string} password Mot de passe de l'utilisateur
	 * @apiParam {string} prenom Prénom de l'utilisateur
	 * @apiParam {string} nom Nom de l'utilisateur
	 * @apiParam {int} id_e Identifiant de la collectivité de base de l'utilisateur (défaut 0)
	 * @apiParam {string} email Email de l'utilisateur
	 * @apiParam {boolean} create Flag permettant la création de l'utilisateur si aucun autre utilisateur ne porte le même nom
	 * 						(faux par défaut)
	 * @apiParam {boolean} dont_delete_certificate_if_empty Ne supprime pas le certificat si celui-ci est vide
	 *
	 * @apiSuccess {string} result ok si tout est ok
	 * @apiSuccess {int} id_u Identifiant de l'utilisateur
	 */
	public function editAction() {
		$createUtilisateur = $this->getFromRequest('create');
		if ($createUtilisateur){
			return $this->createAction();
		}

		$data = $this->getRequest();

		$utilisateur = $this->utilisateur;

		$infoUtilisateurExistant = $this->utilisateur->getUserFromData($data);


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
		$nom = $infoUtilisateurExistant['nom'];
		$prenom = $infoUtilisateurExistant['prenom'];
		$email = $infoUtilisateurExistant['email'];


		$certificat_content = $this->getFileUploader()->getFileContent('certificat');

		// Appel du service métier pour enregistrer la modification de l'utilisateur
		$id_u = $this->editionUtilisateur($id_e, $infoUtilisateurExistant['id_u'], $email, $login, $password, $nom, $prenom, $certificat_content);

		// Si le certificat n'est pas passé, il faut le supprimer de l'utilisateur
		// Faut-il garder ce comportement ou faire des webservices dédiés à la gestion des certificats (au moins la suppression) ?
		if (!$certificat_content && ! $this->getFromRequest('dont_delete_certificate_if_empty',false)) {
			$utilisateur->removeCertificat($infoUtilisateurExistant['id_u']);
		}

		$result['result'] = self::RESULT_OK;
		$result['id_u'] = $id_u;
		return $result;
	}

	/**
	 * @api {get} /detail-utilisateur.php /Utilisateur/detail
	 * @apiDescription Détail d'un utilisateur
	 * @apiGroup Utilisateur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_u Identifiant de l'utilisateur
	 *
	 *
	 * @apiSuccess {string} prenom requis Prénom de l'utilisateur
	 * @apiSuccess {string} nom requis Nom de l'utilisateur
	 * @apiSuccess {int} id_e Identifiant de la collectivité de base de l'utilisateur (défaut 0)
	 * @apiSuccess {string} email Email de l'utilisateur
	 * @apiSuccess {string} certificat Contenu du certificat de l'utilisateur
	 * @apiSuccess {int} id_u L'identifiant de l'utilisateur créé
	 */
	public function detailAction() {
		$id_u = $this->getFromRequest('id_u');
		$infoUtilisateur = $this->verifExists($id_u);
		$this->verifDroit($infoUtilisateur['id_e'], "utilisateur:lecture");
		
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

		$infoUtilisateur = $this->utilisateur->getUserFromData($data);

		$this->verifDroit($infoUtilisateur['id_e'], "utilisateur:edition");

		$this->getRoleUtilisateur()->removeAllRole($infoUtilisateur['id_u']);
		$this->utilisateur->desinscription($infoUtilisateur['id_u']);

		$result['result'] = self::RESULT_OK;
		return $result;
	}


	/**
	 * @api {get} /list-utilisateur.php /Utilisateur/list
	 * @apiDescription  Liste les utilisateurs d'une entité
	 * @apiGroup Utilisateur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité (0 par défaut)
	 *
	 * @apiSuccess {Object[]} utilisateur liste des utilisateurs
	 * @apiSuccess {int} id_u identifiant de l'utilisateur
	 * @apiSuccess {string} login
	 * @apiSuccess {string} email
	 *
	 */
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



}