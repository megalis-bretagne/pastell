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
		if ($this->getFromQueryArgs(0) === 'all'){
			return $this->listAllConnecteur();
		}
		$id_e = $this->checkedEntite();


		$id_ce = $this->getFromQueryArgs(2);
		if ($id_ce){
			return $this->detail($id_e,$id_ce);
		}

		return $this->connecteurEntiteSQL->getAll($id_e);
	}

	public function listAllConnecteur(){
		$id_connecteur = $this->getFromQueryArgs(1);
		$global = $this->getFromRequest('global');
		if (! $id_connecteur){
			return $this->connecteurEntiteSQL->getAllForPlateform();
		}
		return $this->connecteurEntiteSQL->getAllById($id_connecteur);
	}

	public function detail($id_e,$id_ce) {
		$result = $this->checkedConnecteur($id_e,$id_ce);
        if ('file'==$this->getFromQueryArgs(3)){
            echo "file";
            return $this->getFichier($id_ce);
        }

		$donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
		$result['data'] = $donneesFormulaire->getRawData();
		$result['action-possible'] = $this->actionPossible->getActionPossibleOnConnecteur($id_ce, $this->getUtilisateurId());
		return $result;
	}

    public function getFichier($id_ce){
        $field = $this->getFromQueryArgs(4);
        $num = $this->getFromQueryArgs(5)?:0;
        $donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

        $file_path = $donneesFormulaire->getFilePath($field,$num);
        $file_name_array = $donneesFormulaire->get($field);
        if (empty($file_name_array[$num])){
            throw new NotFoundException("Ce fichier n'existe pas");
        }
        $file_name= $file_name_array[$num];

        if (! file_exists($file_path)){
            throw new Exception("Ce fichier n'existe pas");
        }

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

	public function checkedConnecteur($id_e, $id_ce){
		$this->verifExists($id_ce);
		$result = $this->connecteurEntiteSQL->getInfo($id_ce);
		if ($result['id_e'] != $id_e){
			throw new Exception("Le connecteur $id_ce n'appartient pas à l'entité $id_e");
		}
		return $result;
	}

	public function post() {
		$id_e = $this->checkedEntite();

		$id_connecteur = $this->getFromRequest('id_connecteur');

        $id_ce = $this->getFromQueryArgs(2);
        if ($id_ce){
            return $this->postFile($id_e,$id_ce);
        }


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

		//TODO Ajouter une fonction pour lancer les actions autos sur le connecteur
		//$this->jobManager->setJobForConnecteur($id_ce,$action_name,"création du connecteur");

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

		$content = $this->getFromQueryArgs(3);
		if ($content == 'content'){
			return $this->patchContent();
		}

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


	public function patchContent() {
		$id_e = $this->checkedEntite();
		$id_ce = $this->getFromQueryArgs(2);

		$data = $this->getRequest();

		$this->checkDroit($id_e, "entite:edition");

		unset($data['id_e']);
		unset($data['id_ce']);

		$donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

		$donneesFormulaire->setTabDataVerif($data);

		$donneesFormulaire->saveAllFile($this->getFileUploader());


		foreach($donneesFormulaire->getOnChangeAction() as $action) {
			$this->actionExecutorFactory->executeOnConnecteur($id_ce,$this->getUtilisateurId(),$action, true);
		}

		$result = $this->detail($id_e,$id_ce);
		$result['result'] = self::RESULT_OK;
		return $result;
	}

    public function postFile($id_e,$id_ce) {
        $field_name = $this->getFromQueryArgs(3);
        $file_number = $this->getFromQueryArgs(4)?:0;

        $file_name = $this->getFromRequest('file_name');

        $file_content = $this->getFromRequest('file_content');
        $donneesFormulaire = $this->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->addFileFromData($field_name,$file_name,$file_content,$file_number);

        $result = $this->detail($id_e,$id_ce);
        return $result;
    }

}