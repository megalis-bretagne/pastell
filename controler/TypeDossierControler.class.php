<?php

class TypeDossierControler extends PastellControler {

    //TODO penser à l'import/export
    //TODO (menu du gauche) Type de dossier de base => Type de dossier générique
    //TODO Ajouter le composant "liste déroulante"
    //
    //TODO Permettre l'envoi de plusieurs signatures détachées
    //TODO voir en cas de modification du cheminement ce qui se passe
    //TODO reservé les mots-clé commençant pas ls-*

	public function _beforeAction(){
		parent::_beforeAction();
		$this->{'menu_gauche_template'} = "ConfigurationMenuGauche";
		$this->{'menu_gauche_select'} = "TypeDossier/list";
		$this->verifDroit(0,"system:lecture");
	}

	/**
	 * @return TypeDossierSQL
	 */
	private function getTypeDossierSQL(){
		return $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
	}

	/**
	 * @return TypeDossierDefinition
	 */
	private function getTypeDossierDefinition(){
		return $this->getObjectInstancier()->getInstance(TypeDossierDefinition::class);
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
		$this->{'page_title'}= "Création d'un type de dossier personnalisé";
		$this->{'menu_gauche_select'} = "TypeDossier/list";
		$this->{'template_milieu'}= "TypeDossierEdition";
		$this->renderDefault();
	}

	public function doEditionAction(){
		$this->verifDroit(0,"system:edition");

		//TODO vérifier qu'il n'y a pas de conflit sur l'id_type_dossier

		$id_t = $this->getPostOrGetInfo()->getInt('id_t');
		$id_type_dossier = $this->getPostOrGetInfo()->get('id_type_dossier');

		$this->getTypeDossierSQL()->edit($id_t,$id_type_dossier);

		if ($id_t){
			$this->setLastMessage("Modification de l'identifiant du type de dossier personnalié $id_type_dossier");
		} else {
			$this->setLastMessage("Création du type de dossier personnalisé $id_type_dossier");
		}

		//TODO rediriger vers la page du type de dossier personnalisé
		$this->redirect("/TypeDossier/list");
	}

	public function deleteAction(){
		//TODO ajouté une page de confirmation !
		$this->verifDroit(0,"system:edition");
		//TODO vérifier qu'on a pas des dossiers basé sur ce type de dossier
		$id_t = $this->getPostOrGetInfo()->getInt('id_t');

		$this->getTypeDossierSQL()->delete($id_t);
		$this->setLastMessage("Le type de dossier à été supprimé");
		$this->redirect("/TypeDossier/list");
	}

	private function commonEdition(){
		$this->verifDroit(0,"system:edition");
		$this->{'id_t'} = $this->getPostOrGetInfo()->getInt('id_t');
		$this->{'type_de_dossier_info'} = $this->getTypeDossierSQL()->getInfo($this->{'id_t'});
		$this->{'type_dossier_definition'} = $this->getTypeDossierDefinition()->getInfo($this->{'id_t'});
		$this->{'page_title'}= "Type de dossier personnalisé {$this->{'type_de_dossier_info'}['id_type_dossier']}";
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
		$this->getTypeDossierDefinition()->editLibelleInfo($this->{'id_t'},$nom,$type,$description);
		$this->setLastMessage("Les données ont été sauvegardées");
		$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
	}

	/**
	 * @throws NotFoundException
	 */
	public function editionElementAction(){
		$this->commonEdition();
		$element_id = $this->getPostOrGetInfo()->get('element_id');
		$this->{'element_info'} = $this->getTypeDossierDefinition()->getElementInfo($this->{'id_t'},$element_id);
		$this->{'template_milieu'}= "TypeDossierEditionElement";
		$this->renderDefault();
	}

	/**
	 * @throws Exception
	 */
	public function doEditionElementAction(){
		$this->commonEdition();
		try {
			$this->getTypeDossierDefinition()->editionElement($this->{'id_t'}, $this->getPostOrGetInfo());
		} catch (Exception $e){
			$this->setLastMessage($e->getMessage());
			$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
		}
		$this->setLastMessage("Les données ont été sauvegardées");
		$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
	}


	public function deleteElementAction(){
		$this->commonEdition();
		$element_id = $this->getPostOrGetInfo()->get('element_id');
		try {
			$this->getTypeDossierDefinition()->deleteElement($this->{'id_t'}, $element_id);
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

		$this->{'file_field_list'}= $this->getTypeDossierDefinition()->getFieldWithType($this->{'id_t'},'file');
		$this->{'multi_file_field_list'}= $this->getTypeDossierDefinition()->getFieldWithType($this->{'id_t'},'multi_file');
		$this->{'etape_info'} = $this->getTypeDossierDefinition()->getEtapeInfo($this->{'id_t'},$num_etape);
		$this->{'template_milieu'}= "TypeDossierEditionEtape";
		$this->renderDefault();
	}

	public function doEditionEtapeAction(){
		$this->commonEdition();
		try {
			$this->getTypeDossierDefinition()->editionEtape($this->{'id_t'}, $this->getPostOrGetInfo());
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
			$this->getTypeDossierDefinition()->deleteEtape($this->{'id_t'}, $num_etape);
		} catch (Exception $e){
			$this->setLastMessage($e->getMessage());
			$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
		}
		$this->setLastMessage("L'étape à été supprimée");
		$this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
	}

	public function sortElementAction(){
	    $this->commonEdition();
	    $tr = $this->getPostInfo()->get("tr");
        $this->getTypeDossierDefinition()->sortElement($this->{'id_t'},$tr);
        print_r($tr);
	    echo "OK";
    }

    public function sortEtapeAction(){
        $this->commonEdition();
        $tr = $this->getPostInfo()->get("tr");
        $this->getTypeDossierDefinition()->sortEtape($this->{'id_t'},$tr);
        print_r($tr);
        echo "OK";
    }


}