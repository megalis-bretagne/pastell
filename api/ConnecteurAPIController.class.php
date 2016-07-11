<?php

class ConnecteurAPIController extends BaseAPIController {

	private $fluxControler;

	private $donneesFormulaireFactory;

	private $connecteurEntiteSQL;

	private $actionPossible;

	private $fluxEntiteSQL;

	private $actionExecutorFactory;

	private $connecteurFactory;

	private $connecteurDefinitionFiles;

	private $jobManager;

	public function __construct(
		DonneesFormulaireFactory $donneesFormulaireFactory,
		ConnecteurEntiteSQL $connecteurEntiteSQL,
		ActionPossible $actionPossible,
		FluxControler $fluxControler,
		FluxEntiteSQL $fluxEntiteSQL,
		ActionExecutorFactory $actionExecutorFactory,
		ConnecteurFactory $connecteurFactory,
		ConnecteurDefinitionFiles $connecteurDefinitionFiles,
		JobManager $jobManager

	) {
		$this->donneesFormulaireFactory = $donneesFormulaireFactory;
		$this->connecteurEntiteSQL = $connecteurEntiteSQL;
		$this->actionPossible = $actionPossible;
		$this->fluxControler = $fluxControler;
		$this->fluxEntiteSQL = $fluxEntiteSQL;
		$this->actionExecutorFactory = $actionExecutorFactory;
		$this->connecteurFactory = $connecteurFactory;
		$this->connecteurDefinitionFiles = $connecteurDefinitionFiles;
		$this->jobManager = $jobManager;
	}

	private function verifExists($id_ce){
		$info = $this->connecteurEntiteSQL->getInfo($id_ce);
		if ( ! $info) {
			throw new Exception("Ce connecteur n'existe pas.");
		}
	}

	/**
	 * @api {get} /Connecteur/create /Connecteur/create
	 * @apiDescription Crée un connecteur pour une entité (anciennement create-connecteur-entite.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 * @apiParam {string} id_connecteur Identifiant du connecteur
	 * @apiParam {string} libelle Libellé du connecteur
	 *
	 * @apiSuccess {int} id_ce identifiant du connecteur créé
	 */
	public function createAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_connecteur = $this->getFromRequest('id_connecteur');
		$libelle = $this->getFromRequest('libelle');

		$this->verifDroit($id_e, "entite:edition");

		if (!$libelle){
			throw new Exception("Le libellé est obligatoire.");
		}

		if ($id_e){
			$connecteur_info = $this->connecteurDefinitionFiles->getInfo($id_connecteur);
		} else {
			$connecteur_info = $this->connecteurDefinitionFiles->getInfoGlobal($id_connecteur);
		}

		if (!$connecteur_info){
			throw new Exception("Aucun connecteur de ce type.");
		}

		$id_ce =  $this->connecteurEntiteSQL->addConnecteur($id_e,$id_connecteur,$connecteur_info['type'],$libelle);
		$this->jobManager->setJobForConnecteur($id_ce,"création du connecteur");


		$result['id_ce'] = $id_ce;
		return $result;
	}

	/**
	 * @api {get} /Connecteur/delete /Connecteur/delete
	 * @apiDescription Supprime un connecteur pour une entité (anciennement  delete-connecteur-entite.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 * @apiParam {int} id_ce Identifiant du connecteur
	 *
	 * @apiSuccess {string} result ok
	 */
	public function deleteAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_ce = $this->getFromRequest('id_ce');

		$this->verifDroit($id_e, "entite:edition");

		$this->verifExists($id_ce);
		$id_used = $this->fluxEntiteSQL->isUsed($id_ce);

		if ($id_used){
			throw new Exception("Ce connecteur est utilisé par des flux :  " . implode(", ",$id_used));
		}

		$donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
		$donneesFormulaire->delete();

		$this->connecteurEntiteSQL->delete($id_ce);
		$this->jobManager->deleteConnecteur($id_ce);

		$result['result'] = self::RESULT_OK;
		return $result;
	}

	/**
	 * @api {get} /Connecteur/edit /Connecteur/edit
	 * @apiDescription Edite un connecteur pour une entité (anciennement modif-connecteur-entite.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 * @apiParam {int} id_ce Identifiant du connecteur
	 * @apiParam {string} libelle Libellé
	 * @apiParam {string} frequence_en_minute Attente entre deux appels du connecteur pour un flux
	 * @apiParam {string} verrou Identifiant du verrou utilisé
	 *
	 * @apiSuccess {string} result ok
	 */
	public function editAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_ce = $this->getFromRequest('id_ce');
		$libelle = $this->getFromRequest('libelle');
		$frequence_en_minute = $this->getFromRequest('frequence_en_minute',1);
		$id_verrou = $this->getFromRequest('id_verrou','');
		$this->verifDroit($id_e, "entite:edition");

		$this->verifExists($id_ce);
		if ( ! $libelle) {
			throw new Exception ("Le libellé est obligatoire.");
		}
		$this->connecteurEntiteSQL->edit($id_ce,$libelle,$frequence_en_minute,$id_verrou);
		$result['result']=self::RESULT_OK;
		return $result;
	}


	/**
	 * @api {get}  /Connecteur/detail /Connecteur/detail
	 * @apiDescription Détail d'un connecteur pour une entité (anciennement /detail-connecteur-entite.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 * @apiParam {int} id_ce Identifiant du connecteur
	 *
	 * @apiSuccess {string} libelle Libellé
	 * @apiSuccess {string} frequence_en_minute Attente entre deux appels du connecteur pour un flux
	 * @apiSuccess {string} verrou Identifiant du verrou utilisé
	 *
	 * @apiSuccess {string[]} data ensemble des élements de la configuration du connecteur
	 * @apiSuccess {string[]} action-possible ensemble des actions possibles sur le connecteur
	 */
	public function detailAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_ce = $this->getFromRequest('id_ce');
		$this->verifDroit($id_e, "entite:lecture");

		$this->verifExists($id_ce);
		$result = $this->connecteurEntiteSQL->getInfo($id_ce);

		$donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

		$result['data'] = $donneesFormulaire->getRawData();
		$result['action-possible'] = $this->actionPossible->getActionPossibleOnConnecteur($id_ce, $this->getUtilisateurId());

		return $result;
	}

	/**
	 * @api {get}  /Connecteur/list /Connecteur/list
	 * @apiDescription Liste les connecteurs d'une entité (was: /list-connecteur-entite.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 *
	 * @apiSuccess {Object[]} connecteur Liste de connecteur
	 * @apiSuccess {int} id_ce Identifiant du connecteur
	 * @apiSuccess {string} libelle Libellé
	 * @apiSuccess {string} frequence_en_minute Attente entre deux appels du connecteur pour un flux
	 * @apiSuccess {string} verrou Identifiant du verrou utilisé
	 *
	 */
	public function listAction() {
		$id_e = $this->getFromRequest('id_e');
		$this->verifDroit($id_e, "entite:lecture");

		$result = $this->connecteurEntiteSQL->getAll($id_e);
		return $result;
	}

	/**
	 * @api {get}  /Connecteur/createAssociation /Connecteur/createAssociation
	 * @apiDescription Associe un connecteur avec un flux (was: /create-flux-connecteur.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 * @apiParam {int} id_ce Identifiant du connecteur
	 * @apiParam {string} flux Identifiant du flux
	 * @apiParam {string} type Famille de connecteur
	 *
	 * @apiSuccess {int} id_fe identifiant de l'association connecteur/flux
	 *
	 */
	public function associateFluxAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_ce = $this->getFromRequest('id_ce');
		$flux = $this->getFromRequest('flux');
		$type = $this->getFromRequest('type');

		$this->verifDroit($id_e, "entite:edition");

		$id_fe = $this->fluxControler->editionModif($id_e, $flux, $type, $id_ce);

		$result['id_fe'] = $id_fe;
		return $result;
	}

	/**
	 * @api {get} /Connecteur/deleteAssociation /Connecteur/deleteAssociation
	 * @apiDescription Supprime une association entre un connecteur et un flux (was: delete-flux-connecteur.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 * @apiParam {int} id_fe Identifiant de l'association
	 *
	 * @apiSuccess {string} result ok
	 *
	 */
	public function deleteFluxConnecteurAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_fe = $this->getFromRequest('id_fe');

		$this->verifDroit($id_e, "entite:edition");

		$fluxEntiteSQL = $this->fluxEntiteSQL;
		$infoFluxConnecteur = $fluxEntiteSQL->getConnecteurById($id_fe);

		if (!$infoFluxConnecteur) {
			throw new Exception("Le connecteur-flux n'existe pas : {id_fe=$id_fe}");
		}

		if ($id_e != $infoFluxConnecteur['id_e']) {
			throw new Exception("Le connecteur-flux n'existe pas sur l'entité spécifié : {id_fe=$id_fe, id_e=$id_e}");
		}

		$fluxEntiteSQL->removeConnecteur($id_fe);

		$result['result'] = self::RESULT_OK;
		return $result;
	}

	/**
	 * @api {get}  /Connecteur/recherche /Connecteur/recherche
	 * @apiDescription Recherche des association flux/connecteur (was: /list-flux-connecteur.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 * @apiParam {string} type Famille de connecteur
	 * @apiParam {string} flux Flux
	 *
	 * @apiSuccess {Object[]} flux_entite liste d'association
	 *
	 */
	public function rechercheAction() {
		$id_e = $this->getFromRequest('id_e');
		$flux = $this->getFromRequest('flux',null);
		$type = $this->getFromRequest('type',null);

		$this->verifDroit($id_e, "entite:lecture");

		$result = $this->fluxEntiteSQL->getAllFluxEntite($id_e, $flux, $type);
		return $result;
	}

	/**
	 * @api {get} /Connecteur/editContent /Connecteur/editContent
	 * @apiDescription Edite le contenu d'un conncecteur (was: /edit-connecteur-entite.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 * @apiParam {int} id_ce identifiant du connecteur
	 * @apiParam {string[]} data donnée à édité, il est également possible d'envoyer des fichiers dans les post-data
	 *
	 *
	 * @apiSuccess {string} result ok
	 *
	 */
	public function editContentAction() {
		$data = $this->getRequest();

		$id_e = $data['id_e'];
		$id_ce = $data['id_ce'];

		$this->verifDroit($id_e, "entite:edition");

		unset($data['id_e']);
		unset($data['id_ce']);

		$donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

		$donneesFormulaire->setTabDataVerif($data);

		$donneesFormulaire->saveAllFile($this->getFileUploader());


		foreach($donneesFormulaire->getOnChangeAction() as $action) {
			$this->actionExecutorFactory->executeOnConnecteur($id_ce,$this->getUtilisateurId(),$action, true);
		}

		$result['result'] = self::RESULT_OK;
		return $result;
	}


	/**
	 * @api {get} /Connecteur/action /Connecteur/action
	 * @apiDescription Déclenche une action sur un connecteur (was: /action-connecteur-entite.php)
	 * @apiGroup Connecteur
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e Identifiant de l'entité
	 * @apiParam {string} type Famille du connecteur
	 * @apiParam {string} flux Flux
	 * @apiParam {string} action action à déclencher
	 * @apiParam {string[]} action_params Paramètre de l'action
	 *
	 *
	 * @apiSuccess {string} result résultat de l'execution
	 * @apiSuccess {string} message Message lié
	 *
	 */
	public function doActionAction() {

		$id_e = $this->getFromRequest('id_e');
		$type_connecteur = $this->getFromRequest('type');

		//WTF ! Il faut que le connecteur soit associé à un flux ??
		$flux = $this->getFromRequest('flux');
		$action = $this->getFromRequest('action');
		$action_params = $this->getFromRequest('action_params',array());


		// La vérification des droits est déléguée au niveau du test sur l'action est-elle possible.
		//$this->verifDroit($id_e, "entite:edition");

		$connecteur_info = $this->fluxEntiteSQL->getConnecteur($id_e, $flux, $type_connecteur);

		if (!$connecteur_info) {
			throw new Exception("Le connecteur de type $type_connecteur n'existe pas pour le flux $flux.");
		}

		$id_ce=$connecteur_info['id_ce'];

		$actionPossible = $this->actionPossible;

		if ( ! $actionPossible->isActionPossibleOnConnecteur($id_ce, $this->getUtilisateurId(), $action)) {
			throw new Exception("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule());
		}

		$result = $this->actionExecutorFactory->executeOnConnecteur($id_ce,$this->getUtilisateurId(),$action, true, $action_params);
		$message = $this->actionExecutorFactory->getLastMessage();

		if (! $result){
			throw new Exception($message);
		}

		return array("result" => $result, "message"=>$message);
	}

	//WTF ??
	public function infoAction() {

		throw new Exception("Cette ancienne fonction de l'API semble créer un trou de sécurité (appel de n'importe quelle fonction de n'importe quel connecteur ?");

		/*$id_e = $this->getFromRequest('id_e');
		$typeConnecteur = $this->getFromRequest('typeConnecteur');
		$flux = $this->getFromRequest('flux');
		$methode_name = $this->getFromRequest('methode_name');
		$params = $this->getFromRequest('params');

		$this->verifDroit($id_e, "entite:lecture");
		$conn = $this->connecteurFactory->getConnecteurByType($id_e, $flux, $typeConnecteur);
		if (!$conn) {
			throw new Exception ("Aucun connecteur de type $typeConnecteur est défini pour le type $flux.");
		}
		if (!method_exists($conn, $methode_name)) {
			throw new Exception("La méthode $methode_name n'existe pas pour le connecteur.");
		}
		return call_user_func_array(array($conn, $methode_name), $params);*/
	}



}