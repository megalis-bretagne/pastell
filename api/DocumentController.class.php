<?php

class DocumentController extends BaseAPIController
{

	private $documentActionEntite;

	private $document;

	private $donneesFormulaireFactory;

	private $actionPossible;

	private $documentEntite;

	private $actionCreatorSQL;

	private $documentTypeFactory;

	private $actionExecutorFactory;

	/** @var DocumentControler */
	private $documentControler;

	public function __construct(
		DocumentActionEntite $documentActionEntite,
		Document $document,
		DonneesFormulaireFactory $donneesFormulaireFactory,
		ActionPossible $actionPossible,
		DocumentEntite $documentEntite,
		ActionCreatorSQL $actionCreatorSQL,
		DocumentTypeFactory $documentTypeFactory,
		ActionExecutorFactory $actionExecutorFactory,
		DocumentControler $documentControler
	)
	{
		$this->documentActionEntite = $documentActionEntite;
		$this->document = $document;
		$this->donneesFormulaireFactory = $donneesFormulaireFactory;
		$this->actionPossible = $actionPossible;
		$this->documentEntite = $documentEntite;
		$this->actionCreatorSQL = $actionCreatorSQL;
		$this->documentTypeFactory = $documentTypeFactory;
		$this->actionExecutorFactory = $actionExecutorFactory;
		$this->documentControler = $documentControler;
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
	 *                        cette sortie est déprécié et sera supprimé dans une prochaine version de Pastell
	 *
	 */
	public function listAction()
	{
		$id_e = $this->getFromRequest('id_e', 0);
		$type = $this->getFromRequest('type', '');
		$offset = $this->getFromRequest('offset', 0);
		$limit = $this->getFromRequest('limit', 100);

		$this->verifDroit($id_e, "$type:lecture");

		return $this->documentActionEntite->getListDocument($id_e, $type, $offset, $limit);
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
	public function detailAction()
	{
		$id_e = $this->getFromRequest('id_e', 0);
		$id_d = $this->getFromRequest('id_d', 0);
		return $this->detail($id_e, $id_d);
	}

	private function detail($id_e, $id_d)
	{
		$info = $this->document->getInfo($id_d);
		$result['info'] = $info;

		$this->verifDroit($id_e, $info['type'] . ":edition");

		$donneesFormulaire = $this->donneesFormulaireFactory->get($id_d, $info['type']);

		$result['data'] = $donneesFormulaire->getRawData();
		$result['action_possible'] = $this->actionPossible->getActionPossible($id_e, $this->getUtilisateurId(), $id_d);
		$result['last_action'] = $this->documentActionEntite->getLastActionInfo($id_e, $id_d);

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
	public function detailAllAction()
	{
		$id_e = $this->getFromRequest('id_e', 0);
		$all_id_d = $this->getFromRequest('id_d', 0);
		if (!is_array($all_id_d)) {
			throw new Exception("Le paramètre id_d[] ne semble pas valide");
		}

		$max_execution_time = ini_get('max_execution_time');
		$result = array();
		foreach ($all_id_d as $id_d) {
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
	public function createAction()
	{
		$id_e = $this->getFromRequest('id_e', 0);
		$type = $this->getFromRequest('type', '');

		$this->verifDroit($id_e, "$type:edition");

		$id_d = $this->document->getNewId();
		$this->document->save($id_d, $type);
		$this->documentEntite->addRole($id_d, $id_e, "editeur");

		$this->actionCreatorSQL->addAction($id_e, $this->getUtilisateurId(), Action::CREATION, "Création du document [webservice]", $id_d);

		$info['id_d'] = $id_d;
		return $info;
	}

	/**
	 * @api {get} /external-data.php /Document/externalData
	 * @apiDescription Récupération des choix possibles pour un champs "données externes" du document
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {int} id_d requis Identifiant du document (retourné par list-entite)
	 * @apiParam {string} field requis Identifiant du champs à récupérer
	 * @apiParam {string} type requis Identifiant du type de flux (retourné par document-type)
	 * @apiSuccess {variable} output  Information supplémentaire sur la valeur possible (éventuellement sous forme de tableau associatif)
	 *
	 */
	public function externalDataAction()
	{
		$id_e = $this->getFromRequest('id_e', 0);
		$id_d = $this->getFromRequest('id_d', 0);
		$field = $this->getFromRequest('field', 0);

		$info = $this->document->getInfo($id_d);

		$this->verifDroit($id_e, "{$info['type']}:edition");

		$documentType = $this->documentTypeFactory->getFluxDocumentType($info['type']);
		$formulaire = $documentType->getFormulaire();
		$theField = $formulaire->getField($field);

		if (!$theField) {
			throw new Exception("Type $field introuvable");
		}

		$action_name = $theField->getProperties('choice-action');
		return $this->actionExecutorFactory->displayChoice($id_e, $this->getUtilisateurId(), $id_d, $action_name, true, $field);
	}

	/**
	 * @api {get} /recherche-document.php /Document/recherche
	 * @apiDescription Recherche multi-critère dans la liste des documents
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {string} type Identifiant du type de flux (retourné par document-type)
	 * @apiParam {int} offset Numéro de la première ligne à retourner (0 par défaut)
	 * @apiParam {int} limit Nombre maximum de lignes à retourner (100 par défaut)
	 * @apiParam {string} lastetat Dernier état du document
	 * @apiParam {string} last_state_begin Date du passage au dernier état du document le plus ancien(date iso)
	 * @apiParam {string} last_state_end date du passage au dernier état du document le plus récent(date iso)
	 * @apiParam {string} etatTransit le document doit être passé dans cet état
	 * @apiParam {string} state_begin date d'entrée la plus ancienne de l'état etatTransit
	 * @apiParam {string} state_end date d'entrée la plus récente de l'état etatTransit
	 * @apiParam {string} tri critère de tri parmi last_action_date, title et entite
	 * @apiParam {string} search l'objet du document doit contenir la chaine indiquée
	 *
	 * @apiSuccess {Object[]} document liste de documents pastell
	 * @apiSuccess {int} id_e Identifiant de l'entité
	 * @apiSuccess {string} id_d Identifiant unique du document
	 * @apiSuccess {string} role Rôle de l'entité sur le document (exemple : éditeur)
	 * @apiSuccess {string} last-action Dernière action effectuée sur le document
	 * @apiSuccess {string} last_action_date Date de la dernière action
	 * @apiSuccess {string} type Type de document (identique à l'entrée)
	 * @apiSuccess {string} creation Date de création du document
	 * @apiSuccess {string} modification Date de dernière modification du document
	 * @apiSuccess {int[]} entite Liste des identifiant (id_e) des autres entités qui ont des droits sur ce document
	 *
	 */
	public function rechercheAction()
	{
		//FIXME inverser l'appel (documentController doit appellé cette fonction de l'API)
		$this->documentControler->searchDocument(true, true);
		$list = $this->documentControler->{'listDocument'};
		return $list;
	}

	/**
	 * @api {get} /modif-document.php /Document/edit
	 * @apiDescription Modification d'un document
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {int} id_d Identifiant du document
	 * @apiParam {string} data Toutes les clés correspondantes au clé du type de document.
	 *        Permet également l'enregistrement des fichiers.
	 *        data n'est pas le nom de la clé en mode REST, il faut utiliser id_e, id_d, ...
	 *
	 * @apiSuccess {string} result ok - si l'enregistrement s'est bien déroulé
	 * @apiSuccess {string} formulaire_ok 1 si le formulaire est valide, 0 sinon
	 * @apiSuccess {string} message Message complémentaire
	 *
	 */
	public function editAction()
	{
		$data = $this->getRequest();
		$id_e = $data['id_e'];
		$id_d = $data['id_d'];
		$info = $this->document->getInfo($id_d);
		$this->verifDroit($id_e, "{$info['type']}:edition");

		unset($data['id_e']);
		unset($data['id_d']);

		$donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);
		$actionPossible = $this->actionPossible;

		if (!$actionPossible->isActionPossible($id_e, $this->getUtilisateurId(), $id_d, 'modification')) {
			throw new Exception("L'action « modification »  n'est pas permise");
		}

		$donneesFormulaire->setTabDataVerif($data);
		if ($this->getFileUploader()) {
			$donneesFormulaire->saveAllFile($this->getFileUploader());
		}
		return $this->changeDocumentFormulaire($id_e, $id_d, $info['type'], $donneesFormulaire);
	}

	private function changeDocumentFormulaire($id_e, $id_d, $type, DonneesFormulaire $donneesFormulaire)
	{
		/** @var DocumentType $documentType */
		$documentType = $this->documentTypeFactory->getFluxDocumentType($type);
		$formulaire = $documentType->getFormulaire();

		$titre_field = $formulaire->getTitreField();
		$titre = $donneesFormulaire->get($titre_field);

		$document = $this->document;
		$document->setTitre($id_d, $titre);

		foreach ($donneesFormulaire->getOnChangeAction() as $action) {
			$this->actionExecutorFactory->executeOnDocument($id_e, $this->getUtilisateurId(), $id_d, $action, array(), true);
		}

		if ($this->needChangeEtatToModification($id_e, $id_d, $documentType)) {
			$this->actionCreatorSQL->addAction($id_e, $this->getUtilisateurId(), Action::MODIFICATION, "Modification du document [WS]", $id_d);
		}

		$result['result'] = self::RESULT_OK;
		$result['formulaire_ok'] = $donneesFormulaire->isValidable() ? 1 : 0;
		if (!$result['formulaire_ok']) {
			$result['message'] = $donneesFormulaire->getLastError();
		} else {
			$result['message'] = "";
		}
		return $result;
	}

	public function needChangeEtatToModification($id_e, $id_d, DocumentType $documentType) {
		//FIXME : il y a une dépendance dans un script à plat qui devrait normalement utilisé la fonction de l'API...
		$action_name = $this->documentActionEntite->getLastAction($id_e, $id_d);

		$actionObject = $documentType->getAction();
		$modification_no_change_etat = $actionObject->getProperties($action_name, Action::MODIFICATION_NO_CHANGE_ETAT);

		return !$modification_no_change_etat;
	}

	/**
	 * @api {get} /send-file.php /Document/sendFile
	 * @apiDescription Envoi d'un fichier sur un document (dans le postdata)
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {int} id_d Identifiant du document
	 * @apiParam {string} field_name le nom du champs
	 * @apiParam {string} file_name le nom du fichier
	 * @apiParam {string} file_number le numéro du fichier (pour les fichier multiple)
	 * @apiParam {string} file_content le contenu du fichier
	 *
	 * @apiSuccess {string} result ok - si l'enregistrement s'est bien déroulé
	 * @apiSuccess {string} formulaire_ok 1 si le formulaire est valide, 0 sinon
	 * @apiSuccess {string} message Message complémentaire
	 *
	 */
	public function sendFileAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_d = $this->getFromRequest('id_d');
		$field_name = $this->getFromRequest('field_name');
		$file_name = $this->getFromRequest('file_name');
		$file_number = $this->getFromRequest('file_number', 0);
		$file_content = $this->getFromRequest('file_content');

		$document = $this->document;
		$info = $document->getInfo($id_d);
		$this->verifDroit($id_e, "{$info['type']}:edition");
		$donneesFormulaire = $this->donneesFormulaireFactory->get($id_d, $info['type']);
		$donneesFormulaire->addFileFromData($field_name, $file_name, $file_content, $file_number);
		return $this->changeDocumentFormulaire($id_e, $id_d, $info['type'], $donneesFormulaire);
	}

	/**
	 * @api {get} /receive-file.php /Document/receiveFile
	 * @apiDescription Récupère le contenu d'un document (via JSON !)
	 * @apiDeprectated Ne plus utiliser
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {int} id_d Identifiant du document
	 * @apiParam {string} field_name le nom du champs
	 * @apiParam {string} file_number le numéro du fichier (pour les fichier multiple)
	 *
	 * @apiSuccess {string} file_name ok - le nom du fichier
	 * @apiSuccess {string} file_content le contenu du fichier
	 *
	 */
	public function receiveFileAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_d = $this->getFromRequest('id_d');
		$field_name = $this->getFromRequest('field_name');
		$file_number = $this->getFromRequest('file_number');

		$document = $this->document;
		$info = $document->getInfo($id_d);
		$this->verifDroit($id_e, "{$info['type']}:lecture");
		$donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);
		$result['file_name'] = $donneesFormulaire->getFileName($field_name, $file_number);
		$result['file_content'] = $donneesFormulaire->getFileContent($field_name, $file_number);
		return $result;
	}

	/**
	 * @api {get} /action.php /Document/action
	 * @apiDescription Execute une action sur un document
	 * @apiDeprectated Ne plus utiliser
	 * @apiGroup Document
	 * @apiVersion 1.0.0
	 *
	 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
	 * @apiParam {string} id_d Identifiant du document
	 * @apiParam {string} action Le nom de l'action
	 * @apiParam {string[]} destinataire Tableau contenant l'identifiant des destinataires pour les actions qui le requièrent
	 *
	 * @apiSuccess {int} result 1 si l'action a été correctement exécute. Sinon, une erreur est envoyé
	 * @apiSuccess {string} message "Message complémentaire en cas de réussite"
	 *
	 */
	public function actionAction() {
		$id_e = $this->getFromRequest('id_e');
		$id_d = $this->getFromRequest('id_d');
		$action = $this->getFromRequest('action');
		$id_destinataire = $this->getFromRequest('id_destinataire', array());
		$action_params = $this->getFromRequest('action_params', array());

		$document = $this->document;
		$info = $document->getInfo($id_d);
		$this->verifDroit($id_e, "{$info['type']}:edition");

		$actionPossible = $this->actionPossible;

		if ( ! $actionPossible->isActionPossible($id_e,$this->getUtilisateurId(),$id_d,$action)) {
			throw new Exception("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule());
		}

		$result = $this->actionExecutorFactory->executeOnDocument($id_e,$this->getUtilisateurId(),$id_d,$action,$id_destinataire, true,$action_params);
		$message = $this->actionExecutorFactory->getLastMessage();

		if ( ! $result){
			throw new Exception($message);

		}
		return array("result" => $result,"message"=>$message);
	}

}