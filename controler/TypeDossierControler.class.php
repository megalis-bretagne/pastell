<?php

class TypeDossierControler extends PastellControler {

	public function _beforeAction(){
		parent::_beforeAction();
		$this->{'menu_gauche_template'} = "ConfigurationMenuGauche";
		$this->{'menu_gauche_select'} = "TypeDossier/list";
		$this->verifDroit(0,"system:lecture");
		$this->{'dont_display_breacrumbs'} = true;
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
	 * @throws LastErrorException
	 * @throws LastMessageException
	 * @throws NotFoundException
	 */
	public function editionAction(){
		$this->verifDroit(0,"system:edition");
		$id_t = $this->getPostOrGetInfo()->getInt('id_t');
		$this->{'flux_info'} = $this->getTypeDossierSQL()->getInfo($id_t);

		if ($this->{'flux_info'}['id_type_dossier']){
			$id_type_dossier = $this->{'flux_info'}['id_type_dossier'];

			if ($this->getDocument()->isTypePresent($id_type_dossier)){
				$this->setLastError(
					"Des dossiers du type <b>$id_type_dossier</b> existent déjà sur ce Pastell. Impossible de modifier l'identifiant."
				);
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

		try {
		    $this->getTypeDossierService()->checkTypeDossierId($id_type_dossier);
        } catch (TypeDossierException $e){
            $this->setLastMessage($e->getMessage());
            $this->redirect("/TypeDossier/list");
        }

		$fluxDefinitionFiles = $this->getObjectInstancier()->getInstance(FluxDefinitionFiles::class);

		if ($fluxDefinitionFiles->getInfo($id_type_dossier)){
			$this->setLastMessage("Le type de dossier $id_type_dossier existe déjà sur ce Pastell");
			$this->redirect("/TypeDossier/list");
		}

		if (substr( $id_type_dossier, 0, 8) === "pastell-"){
			$this->setLastMessage("Les noms de flux commençant par <b>pastell-</b> sont interdits");
			$this->redirect("/TypeDossier/list");
		}

        $typeDossierProperties = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
        $this->verifyTypeDossierIsUnused($typeDossierProperties->id_type_dossier);
        $source_type_dossier_id = $typeDossierProperties->id_type_dossier;
        $target_type_dossier_id = $id_type_dossier;

        $typeDossierProperties->id_type_dossier = $id_type_dossier;


		$id_t = $this->getTypeDossierSQL()->edit($id_t,$typeDossierProperties);
		if ($is_new) {
			$this->getTypeDossierService()->editLibelleInfo($id_t, $id_type_dossier, TypeDossierService::TYPE_DOSSIER_CLASSEMENT_DEFAULT, "", "onglet1");
		} else {
		    $this->getTypeDossierService()->rename($source_type_dossier_id, $target_type_dossier_id);
        }

		if (! $is_new){
			$this->setLastMessage("Modification de l'identifiant du type de dossier personnalisé $id_type_dossier");
		} else {
			$this->setLastMessage("Le type de dossier personnalisé $id_type_dossier a été créé");
		}

		$this->redirect("/TypeDossier/detail?id_t=$id_t");
	}

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function deleteAction(){
		$this->commonEdition();

		$id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyTypeDossierIsUnused($id_type_dossier);

		$this->{'template_milieu'}= "TypeDossierDelete";
		$this->renderDefault();
	}

    /**
     * @param $id_type_dossier
     * @throws LastErrorException
     * @throws LastMessageException
     */
    private function verifyTypeDossierIsUnused($id_type_dossier){
        if ($this->getDocument()->isTypePresent($id_type_dossier)){
            $this->setLastError("Le type de dossier {$id_type_dossier} est utilisé par des documents présents dans la base de données.");
            $this->redirect("/TypeDossier/list");
        }

        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);

        $role_list = array_unique(array_merge(
            $roleSQL->getRoleByDroit("$id_type_dossier:lecture"),
            $roleSQL->getRoleByDroit("$id_type_dossier:edition")
        ));

        if ($role_list) {
            if (count($role_list) == 1){
                $this->setLastError(
                    "Le type de dossier <b>{$id_type_dossier}</b> est utilisé par le rôle « {$role_list[0]} »"
                );
            } else {
                $this->setLastError(
                    "Le type de dossier <b>{$id_type_dossier}</b> est utilisé par les rôles suivants ".implode(",",$role_list)
                );
            }
            $this->redirect("/TypeDossier/list");
        }

        $fluxEntiteSQL = $this->getObjectInstancier()->getInstance(FluxEntiteSQL::class);
        $entite_list = $fluxEntiteSQL->getEntiteByFlux($id_type_dossier);
        if ($entite_list){
            $output = [];
            foreach($entite_list as $entite_info){
                $output[] = "{$entite_info['denomination']} (id_e={$entite_info['id_e']})";
            }
            if (count($output) == 1){
                $message = "Le type de dossier <b>{$id_type_dossier}</b> a été associé avec des connecteurs sur l'entité ";
            } else {
                $message = "Le type de dossier <b>{$id_type_dossier}</b> a été associé avec des connecteurs sur les entités : ";
            }
            $this->setLastError(
               $message . implode(", ",$output)

            );
            $this->redirect("/TypeDossier/list");
        }
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws TypeDossierException
     */
	public function doDeleteAction(){
		$this->commonEdition();
        $id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyTypeDossierIsUnused($id_type_dossier);
		$this->getTypeDossierService()->delete($this->{'id_t'});

		$this->setLastMessage("Le type de dossier <b>{$this->{'id_type_dossier'}}</b> a été supprimé");

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
		$this->setLastMessage("Les modifications ont été enregistrées");
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
		$this->setLastMessage("Les modifications ont été enregistrées");
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
		$this->setLastMessage("L'élément a été supprimé");
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
		$this->setLastMessage("Les modifications ont été enregistrées");
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
		$this->setLastMessage("L'étape a été supprimée");
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
        $num_etape = 0;
		try {
			$num_etape = $this->getTypeDossierService()->newEtape($this->{'id_t'}, $this->getPostOrGetInfo());
		} catch (Exception $e){
			$this->setLastMessage($e->getMessage());
			$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
		}

		$etapeInfo = $this->getTypeDossierService()->getEtapeInfo($this->{'id_t'},$num_etape);
		if ($etapeInfo->specific_type_info){
            $this->setLastMessage("L'étape a été créée. Veuillez saisir les propriétés spécifiques de l'étape.");
            $this->redirect("/TypeDossier/editionEtape?id_t={$this->{'id_t'}}&num_etape=$num_etape");
        }
        $this->setLastMessage("L'étape a été créée.");
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
		} catch (UnrecoverableException|TypeDossierException $e){
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