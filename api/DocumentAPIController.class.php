<?php

class DocumentAPIController extends BaseAPIController {

	private $documentActionEntite;

	private $document;

	private $donneesFormulaireFactory;

	private $actionPossible;

	private $documentEntite;

	private $actionCreatorSQL;

	private $documentTypeFactory;

	private $actionExecutorFactory;

	private $journal;

	private $utilisateur;

	private $entiteSQL;

	public function __construct(
		DocumentActionEntite $documentActionEntite,
		Document $document,
		DonneesFormulaireFactory $donneesFormulaireFactory,
		ActionPossible $actionPossible,
		DocumentEntite $documentEntite,
		ActionCreatorSQL $actionCreatorSQL,
		DocumentTypeFactory $documentTypeFactory,
		ActionExecutorFactory $actionExecutorFactory,
		Journal $journal,
		Utilisateur $utilisateur,
		EntiteSQL $entiteSQL

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
		$this->journal = $journal;
		$this->utilisateur = $utilisateur;
		$this->entiteSQL = $entiteSQL;
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
		$id_d = $this->getFromQueryArgs(2);
		if ($id_d){
			return $this->detail($id_e,$id_d);
		}

		$all_id_d = $this->getFromRequest('id_d', 0);
		if ($all_id_d){
			return $this->getAll($id_e,$all_id_d);
		}

		$type = $this->getFromRequest('type');
		$offset = intval($this->getFromRequest('offset',0));
		$limit = intval($this->getFromRequest('limit',100));
		$search = $this->getFromRequest('search');
		$lastEtat = $this->getFromRequest('lastetat');
		$last_state_begin = $this->getFromRequest('last_state_begin');
		$last_state_end = $this->getFromRequest('last_state_end');
		$tri =  $this->getFromRequest('tri','date_dernier_etat');
		$etatTransit = $this->getFromRequest('etatTransit');
		$state_begin = $this->getFromRequest('state_begin');
		$state_end = $this->getFromRequest('state_end');
		$sens_tri = $this->getFromRequest('sens_tri','DESC');

		$date_in_fr = $this->getFromRequest('date_in_fr',false);

		if ($date_in_fr) {
			$last_state_begin = getDateIso($last_state_begin);
			$last_state_end = getDateIso($last_state_end);
			$state_begin = getDateIso($state_begin);
			$state_end = getDateIso($state_end);
		}

		if (! $id_e){
			throw new Exception("id_e est obligatoire");
		}
		$this->checkDroit($id_e, "entite:lecture");

		$allDroitEntite = $this->getRoleUtilisateur()->getAllDocumentLecture($this->getUtilisateurId(),$id_e);

		$indexedFieldValue = array();
		if ($type) {
			$documentType = $this->documentTypeFactory->getFluxDocumentType($type);
			$indexedFieldsList = $documentType->getFormulaire()->getIndexedFields();

			foreach($indexedFieldsList as $indexField => $indexLibelle){
				$indexedFieldValue[$indexField] = $this->getFromRequest($indexField);
				if ($documentType->getFormulaire()->getField($indexField)->getType() == 'date' && $date_in_fr ){
					$indexedFieldValue[$indexField] = date_fr_to_iso($this->getFromRequest($indexField));
				}
			}
		}

		$listDocument = $this->documentActionEntite->getListBySearch(
			$id_e,
			$type,
			$offset,
			$limit,
			$search,
			$lastEtat,
			$last_state_begin,
			$last_state_end,
			$tri,
			$allDroitEntite,
			$etatTransit,
			$state_begin,
			$state_end,
			$indexedFieldValue,
			$sens_tri
		);
		return $listDocument;
	}

	private function detail($id_e, $id_d) {
		if ('externalData'==$this->getFromQueryArgs(3)){
			return $this->externalDataAction($id_e,$id_d);
		}
		if ('file'==$this->getFromQueryArgs(3)){
			return $this->getFichier($id_e,$id_d);
		}

		return $this->internalDetail($id_e,$id_d);
	}

	private function internalDetail($id_e,$id_d){
		$info = $this->document->getInfo($id_d);
		$result['info'] = $info;

		$this->checkDroit($id_e, $info['type'] . ":edition");

		$donneesFormulaire = $this->donneesFormulaireFactory->get($id_d, $info['type']);

		$result['data'] = $donneesFormulaire->getRawData();

		$result['action_possible'] = $this->actionPossible->getActionPossible($id_e, $this->getUtilisateurId(), $id_d);

		$result['last_action'] = $this->documentActionEntite->getLastActionInfo($id_e, $id_d);

		return $result;
	}

	public function getAll($id_e,$all_id_d) {
		if (!is_array($all_id_d)) {
			throw new Exception("Le paramètre id_d[] ne semble pas valide");
		}

		$max_execution_time = ini_get('max_execution_time');
		$result = array();
		foreach ($all_id_d as $id_d) {
			ini_set('max_execution_time', $max_execution_time);
			$result[$id_d] = $this->internalDetail($id_e, $id_d);
			$this->donneesFormulaireFactory->clearCache();
			$this->document->clearCache();
		}
		return $result;
	}

	public function post() {
		$id_e = $this->checkedEntite();
		$id_d = $this->getFromQueryArgs(2);
		if ($id_d){
			return $this->postFile($id_e,$id_d);
		}

		$type = $this->getFromRequest('type', '');



		$this->checkDroit($id_e, "$type:edition");

		$id_d = $this->document->getNewId();
		$this->document->save($id_d, $type);
		$this->documentEntite->addRole($id_d, $id_e, "editeur");

		$this->actionCreatorSQL->addAction(
			$id_e,
			$this->getUtilisateurId(),
			Action::CREATION,
			"Création du document [".$this->getCallerType()."]",
			$id_d
		);

        $result = $this->internalDetail($id_e,$id_d);

		$result['id_d'] = $id_d; //Compatibilité...

		return $result;
	}


	public function patch() {
		$data = $this->getRequest();
		$id_e = $this->checkedEntite();
		$id_d = $this->getFromQueryArgs(2);

		$info = $this->document->getInfo($id_d);
		$this->checkDroit($id_e, "{$info['type']}:edition");

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

		$result = $this->changeDocumentFormulaire($id_e, $id_d, $info['type'], $donneesFormulaire);
		$result['content'] = $this->internalDetail($id_e,$id_d);
		return $result;
	}


	private function changeDocumentFormulaire($id_e, $id_d, $type, DonneesFormulaire $donneesFormulaire) {
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


	public function externalDataAction($id_e,$id_d) {

		$field = $this->getFromQueryArgs(4);

		$info = $this->document->getInfo($id_d);

		$this->checkDroit($id_e, "{$info['type']}:edition");

		$documentType = $this->documentTypeFactory->getFluxDocumentType($info['type']);
		$formulaire = $documentType->getFormulaire();
		$theField = $formulaire->getField($field);

		if (!$theField) {
			throw new Exception("Type $field introuvable");
		}

		$action_name = $theField->getProperties('choice-action');
		return $this->actionExecutorFactory->displayChoice($id_e, $this->getUtilisateurId(), $id_d, $action_name, true, $field);
	}

	public function needChangeEtatToModification($id_e, $id_d, DocumentType $documentType) {
		//FIXME : il y a une dépendance dans un script à plat qui devrait normalement utilisé la fonction de l'API...
		$action_name = $this->documentActionEntite->getLastAction($id_e, $id_d);

		$actionObject = $documentType->getAction();
		$modification_no_change_etat = $actionObject->getProperties($action_name, Action::MODIFICATION_NO_CHANGE_ETAT);

		return !$modification_no_change_etat;
	}

	public function getFichier($id_e,$id_d){
		$field = $this->getFromQueryArgs(4);
		$num = $this->getFromQueryArgs(5)?:0;

		$mode_receive = $this->getFromRequest('receive');
		if ($mode_receive){
			return $this->receiveFileAction($id_e,$id_d,$field,$num);
		}
		$info = $this->document->getInfo($id_d);

		$this->checkDroit($id_e,"{$info['type']}:edition");

		$donneesFormulaire = $this->donneesFormulaireFactory->get($id_d,$info['type']);

		$file_path = $donneesFormulaire->getFilePath($field,$num);
		$file_name_array = $donneesFormulaire->get($field);
		if (empty($file_name_array[$num])){
			throw new NotFoundException("Ce fichier n'existe pas");
		}
		$file_name= $file_name_array[$num];

		if (! file_exists($file_path)){
			throw new Exception("Ce fichier n'existe pas");
		}

		$infoUtilisateur = $this->utilisateur->getInfo($this->getUtilisateurId());
		$nom = $infoUtilisateur['prenom']." ".$infoUtilisateur['nom'];

		$this->journal->add(Journal::DOCUMENT_CONSULTATION,$id_e,$id_d,"Consulté","$nom a consulté le document $file_name");


		header_wrapper("Content-type: ".mime_content_type($file_path));
		header_wrapper("Content-disposition: attachment; filename=\"$file_name\"");
		header_wrapper("Expires: 0");
		header_wrapper("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header_wrapper("Pragma: public");

		readfile($file_path);

		exit_wrapper(0);
		//Never reached...
		// @codeCoverageIgnoreStart
		return true;
		// @codeCoverageIgnoreEnd
	}

	public function postFile($id_e,$id_d) {
		if ("action"==$this->getFromQueryArgs(3)){
			return $this->actionAction($id_e,$id_d);
		}
		$field_name = $this->getFromQueryArgs(4);
		$file_number = $this->getFromQueryArgs(5)?:0;

		$file_name = $this->getFromRequest('file_name');

        $fileUploader = $this->getFileUploader();
        $file_content = $fileUploader->getFileContent('file_content');
        if (! $file_content){
            $file_content = $this->getFromRequest('file_content');
        }

		$document = $this->document;
		$info = $document->getInfo($id_d);
		$this->checkDroit($id_e, "{$info['type']}:edition");
		$donneesFormulaire = $this->donneesFormulaireFactory->get($id_d, $info['type']);
		$donneesFormulaire->addFileFromData($field_name, $file_name, $file_content, $file_number);
		$result = $this->changeDocumentFormulaire($id_e, $id_d, $info['type'], $donneesFormulaire);

		$result['content'] = $this->internalDetail($id_e,$id_d);

		return $result;

	}

	public function receiveFileAction($id_e,$id_d,$field_name,$file_number) {
		$document = $this->document;
		$info = $document->getInfo($id_d);
		$this->checkDroit($id_e, "{$info['type']}:lecture");
		$donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);

		$result['file_name'] = $donneesFormulaire->getFileName($field_name, $file_number);
		$result['file_content'] = $donneesFormulaire->getFileContent($field_name, $file_number);

		return $result;
	}


	public function actionAction($id_e,$id_d) {
		$action = $this->getFromQueryArgs(4);
		$id_destinataire = $this->getFromRequest('id_destinataire', array());
		$action_params = $this->getFromRequest('action_params', array());

		$document = $this->document;
		$info = $document->getInfo($id_d);
		$this->checkDroit($id_e, "{$info['type']}:edition");

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