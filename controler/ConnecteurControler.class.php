<?php
class ConnecteurControler extends PastellControler
{

    /**
     * @return ConnecteurDefinitionFiles
     */
    protected function getConnecteurDefinitionFile()
    {
        return $this->getInstance('ConnecteurDefinitionFiles');
    }

    public function _beforeAction()
    {
        parent::_beforeAction();

        $id_e = $this->getGetInfo()->getInt('id_e', 0);
        if (! $id_e) {
            $id_ce = $this->getGetInfo()->getInt('id_ce');

            $connecteur_entite_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
            $id_e = $connecteur_entite_info['id_e'] ?: 0;
        }
        $this->{'id_e'} = $id_e;

        $this->setNavigationInfo($id_e, "Entite/connecteur?");
        $this->{'id_e_menu'} = $id_e;
        $this->{'type_e_menu'} = "";
        $this->{'menu_gauche_template'} = "EntiteMenuGauche";
        $this->{'menu_gauche_select'} = "Entite/connecteur";
    }

    /**
     * @param $id_ce
     * @return array|bool|mixed
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function verifDroitOnConnecteur($id_ce)
    {
        $connecteur_entite_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        if (! $connecteur_entite_info) {
            $this->setLastError("Ce connecteur n'existe pas");
            $this->redirect("/Entite/detail?page=3");
        }
        $this->hasDroitEdition($connecteur_entite_info['id_e']);
        return $connecteur_entite_info;
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doNewAction()
    {
        $recuperateur = $this->getPostInfo();
        $id_e = $recuperateur->getInt('id_e');
        try {
            if ($id_e) {
                $this->hasDroitEdition($id_e);
            }

            $this->apiPost("/entite/$id_e/connecteur");

            $this->setLastMessage("Connecteur ajouté avec succès");
            $this->redirect("/Entite/connecteur?id_e=$id_e");
        } catch (Exception $ex) {
            $this->setLastError($ex->getMessage());
            $this->redirect("/Connecteur/new?id_e=$id_e");
        }
    }


    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doDeleteAction()
    {
        $recuperateur = $this->getPostInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        
        try {
            $info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
            $this->apiDelete("/entite/{$info['id_e']}/connecteur/$id_ce");
            $this->setLastMessage("Le connecteur « {$info['libelle']} » a été supprimé.");
            $this->redirect("/Entite/connecteur?id_e={$info['id_e']}");
        } catch (Exception $ex) {
            $this->setLastError($ex->getMessage());
            $this->redirect("/Connecteur/edition?id_ce=$id_ce");
        }
    }


    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doEditionLibelleAction()
    {
        $recuperateur = $this->getPostInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        $info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        $libelle = $recuperateur->get('libelle');

        try {
            $this->apiPatch("/entite/{$info['id_e']}/connecteur/$id_ce");
        } catch (Exception $ex) {
            $this->getLastError()->setLastError($ex->getMessage());
            $this->redirect("/Connecteur/editionLibelle?id_ce=$id_ce");
        }
        $this->getLastMessage()->setLastMessage("Le connecteur « $libelle » a été modifié.");
        $this->redirect("/Connecteur/edition?id_ce=$id_ce");
    }

    /**
     * @throws Exception
     */
    public function doEditionModifAction()
    {
        $recuperateur = $this->getPostInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        $this->verifDroitOnConnecteur($id_ce);
        
        $fileUploader = new FileUploader();
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->saveTab($recuperateur, $fileUploader, 0);
        
        foreach ($donneesFormulaire->getOnChangeAction() as $action) {
            $result = $this->getActionExecutorFactory()->executeOnConnecteur($id_ce, $this->getId_u(), $action);
            if (! $result) {
                $this->setLastError($this->getActionExecutorFactory()->getLastMessage());
            }
        }

        if ($recuperateur->get('external_data_button')) {
            $this->redirect(urldecode($recuperateur->get('external_data_button')));
        }
        if ($recuperateur->get('ajouter') == 'ajouter') {
            $this->redirect("/Connecteur/editionModif?id_ce=$id_ce");
        } else {
            $this->redirect("/Connecteur/edition?id_ce=$id_ce");
        }
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws Exception
     */
    public function recupFileAction()
    {
        $id_ce = $this->getGetInfo()->getInt('id_ce');
        $field = $this->getGetInfo()->get('field');
        $num = $this->getGetInfo()->getInt('num');

        $this->verifDroitOnConnecteur($id_ce);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
        $filePath = $donneesFormulaire->getFilePath($field, $num);
        if (!$filePath) {
            $this->setLastError("Ce fichier n'existe pas");
            $this->redirect("/Connecteur/edition?id_ce=$id_ce");
        }
        $fileName = $donneesFormulaire->getFileName($field, $num);
        
        header("Content-type: " . mime_content_type($filePath));
        header("Content-disposition: attachment; filename=\"$fileName\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
        readfile($filePath);
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws Exception
     */
    public function deleteFileAction()
    {
        $id_ce = $this->getGetInfo()->getInt('id_ce');
        $field = $this->getGetInfo()->get('field');
        $num = $this->getGetInfo()->getInt('num');

        $this->verifDroitOnConnecteur($id_ce);
        
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
        $donneesFormulaire->removeFile($field, $num);
        
        $this->redirect("/Connecteur/editionModif?id_ce=$id_ce");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function deleteAction()
    {
        $id_ce = $this->getGetInfo()->getInt('id_ce');
        $this->verifDroitOnConnecteur($id_ce);
        
        $this->{'connecteur_entite_info'} = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        
        $this->{'page_title'} = "Suppression du connecteur  « {$this->{'connecteur_entite_info'}['libelle']} »";
        $this->{'template_milieu'} = "ConnecteurDelete";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws Exception
     */
    private function setConnecteurInfo()
    {
        $id_ce = $this->getGetInfo()->getInt('id_ce');
        $this->verifDroitOnConnecteur($id_ce);
        $connecteur_entite_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        $id_e = $connecteur_entite_info['id_e'];
        $entite_info = $this->getEntiteSQL()->getInfo($id_e);

        $this->{'has_definition'} = boolval($this->getConnecteurDefinitionFile()->getInfo($connecteur_entite_info['id_connecteur'], ! boolval($id_e)));

        if ($this->{'has_definition'}) {
            $donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
            $this->{'donneesFormulaire'} = $donneesFormulaire;
            if ($connecteur_entite_info['id_e']) {
                $this->{'action'} = $this->getDocumentTypeFactory()->getEntiteDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
            } else {
                $this->{'action'} = $this->getDocumentTypeFactory()->getGlobalDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
            }
        } else {
            $this->{'donneesFormulaire'} = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
            $this->{'action'} = [];
        }

        $this->{'inject'} = array('id_e' => $id_e,'id_ce' => $id_ce,'id_d' => '','action' => '');
        
        $this->{'my_role'} = "";

        if (! $id_e) {
            $entite_info['denomination'] = "Entité racine";
        }
        $this->{'entite_info'} = $entite_info;
        $this->{'connecteur_entite_info'} = $connecteur_entite_info;
        $this->{'id_ce'} = $id_ce;
        $this->{'id_e'} = $id_e;
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function editionModifAction()
    {
        $this->setConnecteurInfo();
        $this->{'page_title'} = "Configuration du connecteur « {$this->connecteur_entite_info['libelle']} » pour « {$this->{'entite_info'}['denomination']} »";
        $this->{'action_url'} = "Connecteur/doEditionModif";
        $this->{'recuperation_fichier_url'} = "Connecteur/recupFile?id_ce=" . $this->{'id_ce'};
        $this->{'suppression_fichier_url'} = "Connecteur/deleteFile?id_ce=" . $this->{'id_ce'};
        $this->{'page'} = 0;
        $this->{'externalDataURL'} = "Connecteur/externalData" ;
        $this->{'template_milieu'} = "ConnecteurEditionModif";
        $this->renderDefault();
    }

    /**
     * @return JobManager
     */
    private function getJobManager()
    {
        return $this->getObjectInstancier()->getInstance("JobManager");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     * @throws Exception
     */
    public function editionAction()
    {
        $this->setConnecteurInfo();
        $this->{'page_title'} = "Configuration des connecteurs pour « {$this->{'entite_info'}['denomination']} »";
        $this->{'recuperation_fichier_url'} = "Connecteur/recupFile?id_ce=" . $this->{'id_ce'};
        $this->{'template_milieu'} = "ConnecteurEdition";
        $this->{'fieldDataList'} = $this->{'donneesFormulaire'}->getFieldDataListAllOnglet($this->{'my_role'});
        $this->{'job_list'} = $this->getWorkerSQL()->getJobListWithWorkerForConnecteur($this->{'id_ce'});
        $this->{'return_url'} = urlencode("Connecteur/edition?id_ce={$this->{'id_ce'}}");

        $connecteur_info = $this->{'connecteur_entite_info'};

        $connecteurFrequence = new ConnecteurFrequence();
        $connecteurFrequence->type_connecteur =
            $connecteur_info['id_e'] == 0 ? ConnecteurFrequence::TYPE_GLOBAL : ConnecteurFrequence::TYPE_ENTITE;
        $connecteurFrequence->famille_connecteur = $connecteur_info['type'];
        $connecteurFrequence->id_connecteur = $connecteur_info['id_connecteur'];
        $connecteurFrequence->id_ce = $connecteur_info['id_ce'];

        $this->{'connecteurFrequence'} = $this->getJobManager()->getNearestConnecteurFrequence($this->{'id_ce'});
        $this->{'connecteurFrequenceByFlux'} = $this->getJobManager()->getNearestConnecteurForDocument($this->{'id_ce'});

        $this->{'usage_flux_list'} = $this->getFluxEntiteSQL()->getFluxByConnecteur($this->{'id_ce'});
        if ($this->{'has_definition'}) {
            $this->{'action_possible'} = $this->getActionPossible()->getActionPossibleOnConnecteur($this->{'id_ce'}, $this->getId_u());
        } else {
            $this->{'action_possible'} = [];
        }

        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     */
    public function newAction()
    {
        $id_e = $this->getGetInfo()->getInt('id_e');

        $this->verifDroit($id_e, "entite:edition");
        
        $this->{'id_e'} = $id_e;
        $this->{'all_connecteur_dispo'} = $this->getConnecteurDefinitionFile()->getAllByIdE($id_e);
        
        $this->{'page_title'} = "Ajout d'un connecteur";
        $this->{'template_milieu'} = "ConnecteurNew";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function editionLibelleAction()
    {
        $id_ce = $this->getGetInfo()->getInt('id_ce');
        $this->verifDroitOnConnecteur($id_ce);
        
        $this->{'connecteur_entite_info'} = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        
        $this->{'page_title'} = "Modification du connecteur  « {$this->{'connecteur_entite_info'}['libelle']} »";
        $this->{'template_milieu'} = "ConnecteurEditionLibelle";
        $this->renderDefault();
    }


    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function exportAction()
    {
        $id_ce = $this->getGetInfo()->getInt('id_ce');
        $this->verifDroitOnConnecteur($id_ce);

        /** @var ConnecteurFactory $connecteurFactory */
        $connecteurFactory = $this->{'ConnecteurFactory'};

        try {
            $connecteurConfig = $connecteurFactory->getConnecteurConfig($id_ce);
        } catch (Exception $e) {
            $this->setLastError("Export impossible : Impossible de trouver la défintion de ce connecteur");
            $this->redirect("/Connecteur/edition?id_ce=$id_ce");
        }


        $connecteurEntite = $this->getConnecteurEntiteSQL();
        $info = $connecteurEntite->getInfo($id_ce);


        $filename = strtr($info['libelle'], " ", "_") . ".json";

        header("Content-type: application/json");
        header("Content-disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
        echo $connecteurConfig->jsonExport();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function importAction()
    {
        $id_ce = $this->getGetInfo()->getInt('id_ce');

        $this->verifDroitOnConnecteur($id_ce);

        $this->{'connecteur_entite_info'} = $this->getConnecteurEntiteSQL()->getInfo($id_ce);

        $this->{'page_title'} = "Importer des données pour le connecteur  « {$this->{'connecteur_entite_info'}['libelle']} »";
        $this->{'template_milieu'} = "ConnecteurImport";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doImportAction()
    {
        $id_ce = $this->getPostInfo()->getInt('id_ce');

        $this->verifDroitOnConnecteur($id_ce);
        $fileUploader = new FileUploader();
        $file_content = $fileUploader->getFileContent('pser');

        /** @var ConnecteurFactory $connecteurFactory */
        $connecteurFactory = $this->{'ConnecteurFactory'};

        $connecteurConfig = $connecteurFactory->getConnecteurConfig($id_ce);
        try {
            $connecteurConfig->jsonImport($file_content);
            $this->setLastMessage("Les données du connecteur ont été importées");
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
        }

        $this->redirect("/Connecteur/edition?id_ce=$id_ce");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws Exception
     */
    public function actionAction()
    {

        $recuperateur = $this->getPostInfo();

        $action = $recuperateur->get('action');
        $id_ce = $recuperateur->getInt('id_ce', 0);

        $actionPossible = $this->getActionPossible();

        if (! $actionPossible->isActionPossibleOnConnecteur($id_ce, $this->getId_u(), $action)) {
            $this->setLastError("L'action « $action »  n'est pas permise : " . $actionPossible->getLastBadRule());
            $this->redirect("/Connecteur/edition?id_ce=$id_ce");
        }

        $result = $this->getActionExecutorFactory()->executeOnConnecteur($id_ce, $this->getId_u(), $action);

        $message = $this->getActionExecutorFactory()->getLastMessage();

        if (! $result) {
            $this->setLastError($message);
        } else {
            $this->setLastMessage($message);
        }

        $this->redirect("/Connecteur/edition?id_ce=$id_ce");
    }

    /**
     * @throws Exception
     */
    public function externalDataAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        $field = $recuperateur->get('field');

        $connecteur_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        $id_e  = $connecteur_info['id_e'];

        $this->verifDroit($id_e, "entite:edition", "/Connecteur/editionModif?id_ce=$id_ce");

        $documentType = $this->getDocumentTypeFactory()->getDocumentType($id_e, $connecteur_info['id_connecteur']);

        $formulaire = $documentType->getFormulaire();

        $action_name =  $formulaire->getField($field)->getProperties('choice-action');
        $result = $this->getActionExecutorFactory()->displayChoiceOnConnecteur($id_ce, $this->getId_u(), $action_name, $field);
        if (! $result) {
            $this->setLastError($this->getActionExecutorFactory()->getLastMessage());
            $this->redirect("/Connecteur/editionModif?id_ce=$id_ce");
        }
    }

    /**
     * @throws Exception
     */
    public function doExternalDataAction()
    {
        $recuperateur = $this->getPostOrGetInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        $field = $recuperateur->get('field');

        $connecteur_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        $id_e  = $connecteur_info['id_e'];

        $this->verifDroit($id_e, "entite:edition", "/Connecteur/edition?id_ce=$id_ce");

        $documentType = $this->getDocumentTypeFactory()->getDocumentType($id_e, $connecteur_info['id_connecteur']);
        $formulaire = $documentType->getFormulaire();
        $theField = $formulaire->getField($field);

        $action_name = $theField->getProperties('choice-action');
        if (! $this->getActionExecutorFactory()->goChoiceOnConnecteur($id_ce, $this->getId_u(), $action_name, $field)) {
            $this->setLastError($this->getActionExecutorFactory()->getLastMessage());
        }
    }
}
