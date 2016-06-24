<?php

class ConnecteurController extends BaseAPIController {

	//FIXME Inverser la dépendance
	private $connecteurControler;
	private $fluxControler;

	private $donneesFormulaireFactory;

	private $connecteurEntiteSQL;

	private $actionPossible;

	private $fluxEntiteSQL;

	private $actionExecutorFactory;

	private $connecteurFactory;

	public function __construct(
		ConnecteurControler $connecteurControler,
		DonneesFormulaireFactory $donneesFormulaireFactory,
		ConnecteurEntiteSQL $connecteurEntiteSQL,
		ActionPossible $actionPossible,
		FluxControler $fluxControler,
		FluxEntiteSQL $fluxEntiteSQL,
		ActionExecutorFactory $actionExecutorFactory,
		ConnecteurFactory $connecteurFactory
	) {
		$this->connecteurControler = $connecteurControler;
		$this->donneesFormulaireFactory = $donneesFormulaireFactory;
		$this->connecteurEntiteSQL = $connecteurEntiteSQL;
		$this->actionPossible = $actionPossible;
		$this->fluxControler = $fluxControler;
		$this->fluxEntiteSQL = $fluxEntiteSQL;
		$this->actionExecutorFactory = $actionExecutorFactory;
		$this->connecteurFactory = $connecteurFactory;
	}


	public function createAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_connecteur = $this->getFromRequest('id_connecteur');
		$libelle = $this->getFromRequest('libelle');

		// Vérification des droits
		$this->verifDroit($id_e, "entite:edition");

		$id_ce = $this->connecteurControler->nouveau($id_e, $id_connecteur, $libelle);
		$result['id_ce'] = $id_ce;
		return $result;
	}

	public function deleteAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_ce = $this->getFromRequest('id_ce');

		// Vérification des droits
		$this->verifDroit($id_e, "entite:edition");

		$this->connecteurControler->delete($id_ce);
		$result['result'] = self::RESULT_OK;
		return $result;
	}

	public function editAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_ce = $this->getFromRequest('id_ce');
		$libelle = $this->getFromRequest('libelle');
		// Vérification des droits
		$this->verifDroit($id_e, "entite:edition");

		$this->connecteurControler->editionLibelle($id_ce, $libelle);
		$result['result']=self::RESULT_OK;
		return $result;
	}

	public function detailAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_ce = $this->getFromRequest('id_ce');
		$this->verifDroit($id_e, "entite:lecture");

		$result = $this->connecteurEntiteSQL->getInfo($id_ce);

		if (!$result) {
			throw new Exception("Le connecteur n'existe pas.");
		}

		$donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

		$result['data'] = $donneesFormulaire->getRawData();
		$result['action-possible'] = $this->actionPossible->getActionPossibleOnConnecteur($id_ce, $this->getUtilisateurId());

		return $result;
	}

	public function listAction() {
		$id_e = $this->getFromRequest('id_e');
		$this->verifDroit($id_e, "entite:lecture");

		$result = $this->connecteurEntiteSQL->getAll($id_e);
		return $result;
	}

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

	public function deleteFluxConnecteurAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_fe = $this->getFromRequest('id_fe');

		$this->verifDroit($id_e, "entite:edition");

		$fluxEntiteSQL = $this->fluxEntiteSQL;
		$infoFluxConnecteur = $fluxEntiteSQL->getConnecteurById($id_fe);

		if (!$infoFluxConnecteur) {
			throw new Exception("Le connecteur-flux n'existe pas : {id_fe=$id_fe}");
		} else {
			if ($id_e != $infoFluxConnecteur['id_e']) {
				throw new Exception("Le connecteur-flux n'existe pas sur l'entité spécifié : {id_fe=$id_fe, id_e=$id_e}");
			}
		}

		$fluxEntiteSQL->removeConnecteur($id_fe);

		$result['result'] = self::RESULT_OK;
		return $result;
	}


	public function rechercheAction() {
		$id_e = $this->getFromRequest('id_e');
		$flux = $this->getFromRequest('flux',null);
		$type = $this->getFromRequest('type',null);

		$this->verifDroit($id_e, "entite:lecture");

		$result = $this->fluxEntiteSQL->getAllFluxEntite($id_e, $flux, $type);
		return $result;
	}

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



	public function doActionAction() {

		$id_e = $this->getFromRequest('id_e');
		$type_connecteur = $this->getFromRequest('type');
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

		if ($result){
			return array("result" => $result, "message"=>$message);
		} else {
			throw new Exception($message);
		}
	}

	public function infoAction() {
		$id_e = $this->getFromRequest('id_e');
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
		return call_user_func_array(array($conn, $methode_name), $params);
	}



}