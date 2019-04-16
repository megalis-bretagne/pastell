<?php

class TypeDossierControler extends PastellControler {

	public function _beforeAction(){
		parent::_beforeAction();
		$this->{'menu_gauche_template'} = "ConfigurationMenuGauche";
		$this->{'menu_gauche_select'} = "TypeDossier/list";
		$this->verifDroit(0,"system:lecture");
	}


	private function commonEdition(){
		$this->verifDroit(0,"system:edition");
		$this->{'id_t'} = $this->getPostOrGetInfo()->getInt('id_t');
		$this->{'type_de_dossier_info'} = $this->getTypeDossierSQL()->getInfo($this->{'id_t'});
		$this->{'typeDossierProperties'} = $this->getTypeDossierService()->getTypeDossierProperties($this->{'id_t'});
		$this->{'page_title'}= "Type de dossier personnalisé {$this->{'type_de_dossier_info'}['id_type_dossier']}";
		$this->{'id_type_dossier'} = $this->{'type_de_dossier_info'}['id_type_dossier'];

		$typeDossierEtape = $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);
		$this->{'all_etape_type'} = $typeDossierEtape->getAllType();
	}

	/**
	 * @return TypeDossierSQL
	 */
	private function getTypeDossierSQL(){
		return $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
	}

	/**
	 * @return TypeDossierService
	 */
	private function getTypeDossierService(){
		return $this->getObjectInstancier()->getInstance(TypeDossierService::class);
	}

	/**
	 * @return TypeDossierEtapeManager
	 */
	private function getTypeDossierEtapeDefinition(){
		return $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);
	}

	/**
	 * @throws NotFoundException
	 */
	public function listAction(){
		$this->{'type_dossier_list'} = $this->getTypeDossierSQL()->getAll();
		$this->{'droit_edition'} = $this->hasDroit(0,"system:edition");
		$this->{'page_title'}= "Types de dossier personnalisés";
		$this->{'menu_gauche_select'} = "TypeDossier/list";
		$this->{'template_milieu'}= "TypeDossierList";
		$this->renderDefault();
	}

	/**
	 * @throws NotFoundException
	 */
	public function editionAction(){
		$this->verifDroit(0,"system:edition");
		$id_t = $this->getPostOrGetInfo()->getInt('id_t');
		$this->{'flux_info'} = $this->getTypeDossierSQL()->getInfo($id_t);

		if ($this->{'flux_info'}['id_type_dossier']){
			$id_type_dossier = $this->{'flux_info'}['id_type_dossier'];

			if ($this->getDocument()->isTypePresent($id_type_dossier)){
				$this->setLastMessage("Des dossiers du type <b>$id_type_dossier</b> existent déjà sur ce Pastell. Impossible de modifier le nom");
				$this->redirect("/TypeDossier/list");
			}
		}

		$this->{'page_title'}= "Création d'un type de dossier personnalisé";
		$this->{'menu_gauche_select'} = "TypeDossier/list";
		$this->{'template_milieu'}= "TypeDossierEdition";
		$this->renderDefault();
	}

	/**
	 * @throws Exception
	 */
	public function doEditionAction(){
		$this->verifDroit(0,"system:edition");

		$id_t = $this->getPostOrGetInfo()->getInt('id_t');
		$is_new = ! $id_t;
		$id_type_dossier = $this->getPostOrGetInfo()->get('id_type_dossier');

		if (! $id_type_dossier){
			$this->setLastMessage("Aucun identifiant de type de dossier fourni");
			$this->redirect("/TypeDossier/list");
		}

		if (! preg_match("#".TypeDossierService::TYPE_DOSSIER_ID_REGEXP."#",$id_type_dossier)){
			$this->setLastMessage("L'identifiant du type de dossier ne respecte pas l'expression rationnelle : " . TypeDossierService::TYPE_DOSSIER_ID_REGEXP);
			$this->redirect("/TypeDossier/list");
		}

		if (strlen($id_type_dossier)>TypeDossierService::TYPE_DOSSIER_ID_MAX_LENGTH){
			$this->setLastMessage("L'identifiant du type de dossier ne doit pas dépasser " . TypeDossierService::TYPE_DOSSIER_ID_MAX_LENGTH." caractères");
			$this->redirect("/TypeDossier/list");
		}

		$fluxDefinitionFiles = $this->getObjectInstancier()->getInstance(FluxDefinitionFiles::class);

		if ($fluxDefinitionFiles->getInfo($id_type_dossier)){
			$this->setLastMessage("Le type de dossier <b>$id_type_dossier</b> existe déjà sur ce Pastell");
			$this->redirect("/TypeDossier/list");
		}

		if (substr( $id_type_dossier, 0, 8) === "pastell-"){
			$this->setLastMessage("Les noms de flux commençant par <b>pastell-</b> sont interdits");
			$this->redirect("/TypeDossier/list");
		}


		$typeDossierProperties = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
		$typeDossierProperties->id_type_dossier = $id_type_dossier;


		$id_t = $this->getTypeDossierSQL()->edit($id_t,$typeDossierProperties);
		if ($is_new) {
			$this->getTypeDossierService()->editLibelleInfo($id_t, $id_type_dossier, "Flux Généraux", "", "onglet1");
		}

		if (! $is_new){
			$this->setLastMessage("Modification de l'identifiant du type de dossier personnalié $id_type_dossier");
		} else {
			$this->setLastMessage("Le type de dossier personnalisé <b>$id_type_dossier</b> a été créé");
		}

		$this->redirect("/TypeDossier/detail?id_t=$id_t");
	}

	/**
	 * @throws NotFoundException
	 */
	public function deleteAction(){
		$this->commonEdition();

		$id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
		if ($this->getDocument()->isTypePresent($id_type_dossier)){
			$this->setLastError("Le type de dossier <b>{$id_type_dossier}</b> est utilisé par des documents présent dans la base de données : La suppression est impossible.");
			$this->redirect("/TypeDossier/list");
		}
		$this->{'template_milieu'}= "TypeDossierDelete";
		$this->renderDefault();
	}

	public function doDeleteAction(){
		$this->commonEdition();
		if ($this->getDocument()->isTypePresent($this->{'id_type_dossier'})){
			$this->setLastError("Le type de dossier <b>{$this->{'id_type_dossier'}}</b> est utilisé par des documents présent dans la base de données : La suppression est impossible.");
			$this->redirect("/TypeDossier/list");
		}
		$this->getTypeDossierService()->delete($this->{'id_t'});
		$this->setLastMessage("Le type de dossier <b>{$this->{'id_type_dossier'}}</b> à été supprimé");

		$this->getJournal()->addSQL(Journal::TYPE_DOSSIER_EDITION,0,$this->getId_u(),0,false,"Le type de document {$this->{'id_type_dossier'}} (id_t={$this->{'id_t'}}) a été supprimé");

		$this->redirect("/TypeDossier/list");
	}

	/**
	 * @throws NotFoundException
	 */
	public function detailAction(){
		$this->commonEdition();
        $this->{'csrfToken'} = $this->getObjectInstancier()->getInstance(CSRFToken::class);
		$this->{'template_milieu'}= "TypeDossierDetail";
		$this->renderDefault();
	}

	/**
	 * @throws NotFoundException
	 */
	public function editionLibelleAction(){
		$this->commonEdition();
		$this->{'template_milieu'}= "TypeDossierEditionLibelle";
		$this->renderDefault();
	}

	/**
	 * @throws Exception
	 */
	public function doEditionLibelleAction(){
		$this->commonEdition();
		$nom = $this->getPostOrGetInfo()->get('nom');
		$type = $this->getPostOrGetInfo()->get('type');
		$description = $this->getPostOrGetInfo()->get('description');
		$nom_onglet = $this->getPostOrGetInfo()->get('nom_onglet');
		$this->getTypeDossierService()->editLibelleInfo($this->{'id_t'},$nom,$type,$description,$nom_onglet);
		$this->setLastMessage("Les données ont été sauvegardées");
		$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
	}

	/**
	 * @throws NotFoundException
	 */
	public function editionElementAction(){
		$this->commonEdition();
		$element_id = $this->getPostOrGetInfo()->get('element_id');
		$this->{'formulaireElement'} = $this->getTypeDossierService()->getFormulaireElement($this->{'id_t'},$element_id);
		$this->{'template_milieu'}= "TypeDossierEditionElement";
		$this->renderDefault();
	}

	/**
	 * @throws Exception
	 */
	public function doEditionElementAction(){
		$this->commonEdition();
		try {
			$this->getTypeDossierService()->editionElement($this->{'id_t'}, $this->getPostOrGetInfo());
		} catch (Exception $e){
			$this->setLastError($e->getMessage());
			$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
		}
		$this->setLastMessage("Les données ont été sauvegardées");
		$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
	}


	public function deleteElementAction(){
		$this->commonEdition();
		$element_id = $this->getPostOrGetInfo()->get('element_id');
		try {
			$this->getTypeDossierService()->deleteElement($this->{'id_t'}, $element_id);
		} catch (Exception $e){
			$this->setLastMessage($e->getMessage());
			$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
		}
		$this->setLastMessage("L'élément à été supprimé");
		$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
	}

	/**
	 * @throws NotFoundException
	 */
	public function editionEtapeAction(){
		$this->commonEdition();
		$num_etape = $this->getPostOrGetInfo()->get('num_etape',0);

		$this->{'file_field_list'}= $this->getTypeDossierService()->getFieldWithType($this->{'id_t'},'file');
		$this->{'multi_file_field_list'}= $this->getTypeDossierService()->getFieldWithType($this->{'id_t'},'multi_file');
		$this->{'text_field_list'}= $this->getTypeDossierService()->getFieldWithType($this->{'id_t'},'text');

		$this->{'etapeInfo'} = $this->getTypeDossierService()->getEtapeInfo($this->{'id_t'},$num_etape);
		$this->{'formulaire_etape'} = $this->getTypeDossierEtapeDefinition()->getFormulaireConfigurationEtape($this->{'etapeInfo'}->type);

		$this->{'template_milieu'}= "TypeDossierEditionEtape";
		$this->renderDefault();
	}

	public function doEditionEtapeAction(){
		$this->commonEdition();
		try {
			$this->getTypeDossierService()->editionEtape($this->{'id_t'}, $this->getPostOrGetInfo());
			$this->getTypeDossierService()->editionEtape($this->{'id_t'}, $this->getPostOrGetInfo());
		} catch (Exception $e){
			$this->setLastMessage($e->getMessage());
			$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
		}
		$this->setLastMessage("Les données ont été sauvegardées");
		$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
	}

	public function deleteEtapeAction(){
		$this->commonEdition();
		$num_etape = $this->getPostOrGetInfo()->getInt('num_etape');
		try {
			$this->getTypeDossierService()->deleteEtape($this->{'id_t'}, $num_etape);
		} catch (Exception $e){
			$this->setLastMessage($e->getMessage());
			$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
		}
		$this->setLastMessage("L'étape à été supprimée");
		$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
	}

	/**
	 * @throws Exception
	 */
	public function sortElementAction(){
	    $this->commonEdition();
	    $tr = $this->getPostInfo()->get("tr");
        $this->getTypeDossierService()->sortElement($this->{'id_t'},$tr);
        print_r($tr);
	    echo "OK";
    }

	/**
	 * @throws Exception
	 */
    public function sortEtapeAction(){
        $this->commonEdition();
        $tr = $this->getPostInfo()->get("tr");
        $this->getTypeDossierService()->sortEtape($this->{'id_t'},$tr);
        print_r($tr);
        echo "OK";
    }

	/**
	 * @throws NotFoundException
	 */
    public function newEtapeAction(){
    	$this->commonEdition();
		$this->{'template_milieu'}= "TypeDossierNewEtape";
		$this->{'etapeInfo'} = $this->getTypeDossierService()->getEtapeInfo($this->{'id_t'},"new");
		$this->renderDefault();
	}

	public function doNewEtapeAction(){
		$this->commonEdition();
		try {
			$this->getTypeDossierService()->newEtape($this->{'id_t'}, $this->getPostOrGetInfo());
		} catch (Exception $e){
			$this->setLastMessage($e->getMessage());
			$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
		}
		$this->setLastMessage("Les données ont été sauvegardées");
		$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
	}

	/**
	 * @throws UnrecoverableException
	 */
	public function exportAction(){
		$id_t = $this->getPostOrGetInfo()->getInt('id_t');
		$type_dossier_info = $this->getTypeDossierSQL()->getInfo($id_t);
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$data_to_send = $typeDossierImportExport->export($id_t);
		$sendFileToBrowser = $this->getObjectInstancier()->getInstance(SendFileToBrowser::class);
		$sendFileToBrowser->sendData($data_to_send,$type_dossier_info['id_type_dossier'].".json","application/json");
	}

	/**
	 * @throws NotFoundException
	 */
	public function importAction(){
		$this->verifDroit(0,"system:edition");
		$this->{'page_title'}= "Import d'un type de dossier personnalisé";
		$this->{'menu_gauche_select'} = "TypeDossier/list";
		$this->{'template_milieu'}= "TypeDossierImport";
		$this->renderDefault();
	}

	public function doImportAction(){
		$this->verifDroit(0,"system:edition");
		$fileUploader  = $this->getObjectInstancier()->getInstance(FileUploader::class);
		$file_content = $fileUploader->getFileContent("json_type_dossier");

		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);

		$result = [];
		try {
			$result = $typeDossierImportExport->import($file_content);
		} catch (UnrecoverableException $e){
			$this->setLastError($e->getMessage());
			$this->redirect("/TypeDossier/import");
		}

		if ($result['id_type_dossier'] == $result['orig_id_type_dossier']){
			$this->setLastMessage("Le type de dossier  <b>{$result['id_type_dossier']}</b> a été importé.");
		} else {
			$this->setLastMessage(
				"Le type de dossier a été importé avec l'identifiant <b>{$result['id_type_dossier']}</b> car l'identiant original ({$result['orig_id_type_dossier']}) existe déjà sur la plateforme"
			);
		}
		$this->redirect("/TypeDossier/detail?id_t={$result['id_t']}");
	}

}