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

	private $entiteSQL;

	public function __construct(
		DonneesFormulaireFactory $donneesFormulaireFactory,
		ConnecteurEntiteSQL $connecteurEntiteSQL,
		ActionPossible $actionPossible,
		FluxControler $fluxControler,
		FluxEntiteSQL $fluxEntiteSQL,
		ActionExecutorFactory $actionExecutorFactory,
		ConnecteurFactory $connecteurFactory,
		ConnecteurDefinitionFiles $connecteurDefinitionFiles,
		JobManager $jobManager,
		EntiteSQL $entiteSQL

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
		$this->entiteSQL = $entiteSQL;
	}

	private function verifExists($id_ce){
		$info = $this->connecteurEntiteSQL->getInfo($id_ce);
		if ( ! $info) {
			throw new Exception("Ce connecteur n'existe pas.");
		}
	}

	private function checkedEntite(){
		$id_e = $this->getFromQueryArgs(0)?:0;
		if ($id_e && ! $this->entiteSQL->getInfo($id_e)){
			throw new NotFoundException("L'entité $id_e n'existe pas");
		}
		$this->checkDroit($id_e, "entite:lecture");
		return $id_e;
	}

	public function get() {
		$id_e = $this->checkedEntite();
		$id_ce = $this->getFromQueryArgs(2);
		if ($id_ce){
			return $this->detail($id_e,$id_ce);
		}

		return $this->connecteurEntiteSQL->getAll($id_e);
	}

	public function detail($id_e,$id_ce) {
		$result = $this->checkedConnecteur($id_e,$id_ce);
		$donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
		$result['data'] = $donneesFormulaire->getRawData();
		$result['action-possible'] = $this->actionPossible->getActionPossibleOnConnecteur($id_ce, $this->getUtilisateurId());
		return $result;
	}

	public function checkedConnecteur($id_e, $id_ce){
		$this->verifExists($id_ce);
		$result = $this->connecteurEntiteSQL->getInfo($id_ce);
		if ($result['id_e'] != $id_e){
			throw new Exception("erreur");
		}
		return $result;
	}

	public function post() {
		$id_e = $this->checkedEntite();

		$id_connecteur = $this->getFromRequest('id_connecteur');
		$libelle = $this->getFromRequest('libelle');

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

		return $this->detail($id_e,$id_ce);
	}

	public function delete() {
		$id_e = $this->checkedEntite();
		$id_ce = $this->getFromQueryArgs(2);

		$this->checkedConnecteur($id_e,$id_ce);
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

	public function patch() {
		$id_e = $this->checkedEntite();
		$id_ce = $this->getFromQueryArgs(2);

		$this->checkedConnecteur($id_e,$id_ce);

		$libelle = $this->getFromRequest('libelle');
		$frequence_en_minute = $this->getFromRequest('frequence_en_minute',1);
		$id_verrou = $this->getFromRequest('id_verrou','');

		if ( ! $libelle) {
			throw new Exception ("Le libellé est obligatoire.");
		}
		$this->connecteurEntiteSQL->edit($id_ce,$libelle,$frequence_en_minute,$id_verrou);
		$result['result']=self::RESULT_OK;
		return $this->detail($id_e,$id_ce);
	}



	/***/




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

		$this->checkDroit($id_e, "entite:lecture");

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

		$this->checkDroit($id_e, "entite:edition");

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



}