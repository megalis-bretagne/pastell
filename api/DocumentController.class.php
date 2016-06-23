<?php

class DocumentController extends BaseAPIController {

	private $documentActionEntite;

	private $document;

	private $donneesFormulaireFactory;

	private $actionPossible;

	private $documentEntite;

	private $actionCreatorSQL;

	public function __construct(
		DocumentActionEntite $documentActionEntite,
		Document $document,
		DonneesFormulaireFactory $donneesFormulaireFactory,
		ActionPossible $actionPossible,
		DocumentEntite $documentEntite,
		ActionCreatorSQL $actionCreatorSQL
	){
		$this->documentActionEntite = $documentActionEntite;
		$this->document = $document;
		$this->donneesFormulaireFactory = $donneesFormulaireFactory;
		$this->actionPossible = $actionPossible;
		$this->documentEntite = $documentEntite;
		$this->actionCreatorSQL = $actionCreatorSQL;
	}

	/**
	 * @api {get} /list-document.php /Document/list
	 * @apiDescription Listes de documents Pastell d'une entité
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {string} type requis Identifiant du type de flux (retourné par document-type)
	 * @apiParam {int} offset Numéro de la première ligne à retourner (0 par défaut)
	 * @apiParam {int} limit Nombre maximum de lignes à retourner (100 par défaut)
	 * @apiSuccess {Object[]} document Liste de document
	 * @apiSuccess {int} id_e Identifiant numérique de l'entité
	 * @apiSuccess {int} id_d Identifiant unique du document
	 * @apiSuccess {string} last-action Dernière action effectuée sur le document
	 * @apiSuccess {string} last_action_date Date de la dernière action
	 * @apiSuccess {string} type Type de document (identique à l'entrée)
	 * @apiSuccess {string} creation Date de création du document
	 * @apiSuccess {string} modification Date de dernière modification du document
	 * @apiSuccess {id_e[]} entite Contient la même chose que action_possible,
	 * 						cette sortie est déprécié et sera supprimé dans une prochaine version de Pastell
	 *
	 */
	public function listAction(){
		$id_e = $this->getFromRequest('id_e',0);
		$type = $this->getFromRequest('type','');
		$offset = $this->getFromRequest('offset',0);
		$limit = $this->getFromRequest('limit',100);

		$this->verifDroit($id_e,"$type:lecture");

		return $this->documentActionEntite->getListDocument($id_e , $type , $offset, $limit) ;
	}

	/**
	 * @api {get} /detail-document.php /Document/detail
	 * @apiDescription Récupère l'ensemble des informations sur un document Liste également les entités filles.
	 * @apiGroup Document
	 * @apiVersion 2.0.0
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {int} id_d requis Identifiant du document (retourné par list-entite)
	 * @apiSuccess {Object[]} info Reprend les informations disponible sur list-document.php
	 * @apiSuccess {Object[]} data Données issue du formulaire (voir document-type-info.php pour savoir ce qu'il est possible de récupérer)
	 * @apiSuccess {Object[]} action_possible Liste des actions possible (exemple : modification, envoie-tdt, ...)
	 *
	 */
	public function detailAction(){
		$id_e = $this->getFromRequest('id_e',0);
		$id_d = $this->getFromRequest('id_d',0);
		return $this->detail($id_e,$id_d);
	}

	private function detail($id_e,$id_d){
		$info = $this->document->getInfo($id_d);
		$result['info'] = $info;

		$this->verifDroit($id_e,$info['type'].":edition");

		$donneesFormulaire  = $this->donneesFormulaireFactory->get($id_d,$info['type']);

		$result['data'] = $donneesFormulaire->getRawData();
		$result['action_possible'] = $this->actionPossible->getActionPossible($id_e,$this->getUtilisateurId(),$id_d);
		$result['last_action'] = $this->documentActionEntite->getLastActionInfo($id_e,$id_d);

		return $result;
	}

	/**
	 * @api {get} /detail-several-document.php /Document/detailAll
	 * @apiDescription Récupère l'ensemble des informations sur plusieurs documents.
	 * @apiGroup Document
	 * @apiVersion 2.0.0
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {int[]} id_d[] requis Tableau d'identifiants uniques de documents  (retourné par list-document.php)
	 * @apiSuccess {Array[]} array Liste d'objet decrit dans la fonction detail-document.php
	 * @apiSuccess {Object[]} info Reprend les informations disponible sur list-document.php
	 * @apiSuccess {Object[]} data Données issue du formulaire (voir document-type-info.php pour savoir ce qu'il est possible de récupérer)
	 * @apiSuccess {Object[]} action_possible Liste des actions possible (exemple : modification, envoie-tdt, ...)
	 *
	 */
	public function detailAllAction(){
		$id_e = $this->getFromRequest('id_e',0);
		$all_id_d = $this->getFromRequest('id_d',0);
		if (! is_array($all_id_d)){
			throw new Exception("Le paramètre id_d[] ne semble pas valide");
		}

		$max_execution_time= ini_get('max_execution_time');
		$result = array();
		foreach($all_id_d as $id_d) {
			ini_set('max_execution_time', $max_execution_time);
			$result[$id_d] = $this->detail($id_e, $id_d);
			$this->donneesFormulaireFactory->clearCache();
			$this->document->clearCache();
		}
		return $result;
	}

	/**
	 * @api {get} /create-document.php /Document/create
	 * @apiDescription Création d'un document
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {string} type requis Identifiant du type de flux (retourné par document-type)
	 * @apiSuccess {string} id_d  Identifiant unique du document crée.
	 *
	 */
	public function createAction(){
		$id_e = $this->getFromRequest('id_e',0);
		$type = $this->getFromRequest('type','');

		$this->verifDroit($id_e,"$type:edition");

		$id_d = $this->document->getNewId();
		$this->document->save($id_d,$type);
		$this->documentEntite->addRole($id_d,$id_e,"editeur");

		$this->actionCreatorSQL->addAction($id_e,$this->getUtilisateurId(),Action::CREATION,"Création du document [webservice]",$id_d);

		$info['id_d'] = $id_d;
		return $info;
	}

}