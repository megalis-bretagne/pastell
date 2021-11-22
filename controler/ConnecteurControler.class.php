<?php

use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\Alerts\InvalidDigestLength;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\Alerts\InvalidSalt;
use ParagonIE\Halite\Alerts\InvalidType;
use Pastell\Service\Crypto;
use Pastell\Service\Connecteur\ConnecteurHashService;
use Pastell\Service\Connecteur\ConnecteurActionService;
use Pastell\Service\Connecteur\ConnecteurModificationService;

class ConnecteurControler extends PastellControler
{

    /**
     * @return ConnecteurDefinitionFiles
     */
    protected function getConnecteurDefinitionFile()
    {
        return $this->getInstance(ConnecteurDefinitionFiles::class);
    }

    /**
     * @return ConnecteurActionService
     */
    private function getConnecteurActionService(): ConnecteurActionService
    {
        return $this->getObjectInstancier()->getInstance(ConnecteurActionService::class);
    }

    /**
     * @return ConnecteurModificationService
     */
    private function getConnecteurModificationService(): ConnecteurModificationService
    {
        return $this->getObjectInstancier()->getInstance(ConnecteurModificationService::class);
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
        $this->setDroitLectureOnConnecteur($id_e);
    }

    public function hasDroitEdition($id_e): void
    {
        $this->hasConnecteurDroitEdition($id_e);
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

        $result = $this->getConnecteurModificationService()->editConnecteurFormulaire(
            $id_ce,
            $recuperateur,
            new FileUploader(),
            false,
            $this->getConnecteurEntiteSQL()->getInfo($id_ce)['id_e'],
            $this->getId_u(),
            "Modification du connecteur"
        );
        if (! $result) {
            $this->setLastError($this->getConnecteurModificationService()->getLastMessage());
        }

        if ($recuperateur->get('external_data_button')) {
            $this->redirect(urldecode($recuperateur->get('external_data_button')));
        }
        /* On a appuyé sur un bouton "Ajouter un fichier" */
        if ($recuperateur->get('ajouter') == 'ajouter') {
            $fieldSubmitted = $recuperateur->get('fieldSubmittedId');
            $this->getConnecteurActionService()->add(
                $this->getConnecteurEntiteSQL()->getInfo($id_ce)['id_e'],
                $this->getId_u(),
                $id_ce,
                '',
                ConnecteurActionService::ACTION_MODIFFIE,
                "Le fichier $fieldSubmitted a été modifié"
            );
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

        $this->getConnecteurModificationService()->removeFile(
            $id_ce,
            $field,
            $num,
            $this->getConnecteurEntiteSQL()->getInfo($id_ce)['id_e'],
            $this->getId_u(),
            "Le fichier $field a été supprimé"
        );

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

        $this->{'has_definition'} = boolval(
            $this->getConnecteurDefinitionFile()->getInfo($connecteur_entite_info['id_connecteur'], ! boolval($id_e))
        );

        if ($this->{'has_definition'}) {
            $donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
            $this->{'donneesFormulaire'} = $donneesFormulaire;
            if ($connecteur_entite_info['id_e']) {
                $this->{'action'} = $this->getDocumentTypeFactory()
                    ->getEntiteDocumentType($connecteur_entite_info['id_connecteur'])
                    ->getAction();
            } else {
                $this->{'action'} = $this->getDocumentTypeFactory()
                    ->getGlobalDocumentType($connecteur_entite_info['id_connecteur'])
                    ->getAction();
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
        $this->{'page_title'} = "Configuration du connecteur « {$this->connecteur_entite_info['libelle']} » 
            pour « {$this->{'entite_info'}['denomination']} »";
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
        $this->{'connecteurFrequenceByFlux'} = $this->getJobManager()
            ->getNearestConnecteurForDocument($this->{'id_ce'});
        $this->{'connecteur_hash'} = $this->getConnecteurActionService()->getLastHash($this->{'id_ce'});
        $this->{'usage_flux_list'} = $this->getFluxEntiteSQL()->getFluxByConnecteur($this->{'id_ce'});
        if ($this->{'has_definition'}) {
            $this->{'action_possible'} = $this->getActionPossible()
                ->getActionPossibleOnConnecteur($this->{'id_ce'}, $this->getId_u());
        } else {
            $this->{'action_possible'} = [];
        }

        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function etatAction()
    {
        $this->{'id_ce'} = $this->getGetInfo()->getInt('id_ce');
        $this->verifDroitOnConnecteur($this->{'id_ce'});
        $connecteur_entite_info = $this->getConnecteurEntiteSQL()->getInfo($this->{'id_ce'});
        $id_e = $connecteur_entite_info['id_e'];
        $entite_info = $this->getEntiteSQL()->getInfo($id_e);
        if (! $id_e) {
            $entite_info['denomination'] = "Entité racine";
        }
        $this->{'page_title'} = "États du connecteur « {$connecteur_entite_info['libelle']} » 
            pour « {$entite_info['denomination']} »";
        $this->{'offset'} = $this->getPostOrGetInfo()->get('offset', 0);
        $this->{'limit'} = 20;
        $this->{'count'} = $this->getConnecteurActionService()->countByIdCe($this->{'id_ce'});
        $this->{'connecteurAction'} = $this->getConnecteurActionService()
            ->getByIdCe($this->{'id_ce'}, $this->{'offset'}, $this->{'limit'});

        $this->{'template_milieu'} = "ConnecteurEtat";
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
     * @throws NotFoundException
     */
    public function exportAction(): void
    {
        $id_ce = $this->getGetInfo()->getInt('id_ce');
        $this->verifDroitOnConnecteur($id_ce);

        $this->{'id_ce'} = $id_ce;
        $this->{'page_title'} = "Connecteur - Export";
        $this->{'template_milieu'} = "ConnecteurExport";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws CannotPerformOperation
     * @throws InvalidDigestLength
     * @throws InvalidKey
     * @throws InvalidMessage
     * @throws InvalidSalt
     * @throws InvalidType
     */
    public function doExportAction(): void
    {
        $id_ce = $this->getPostInfo()->getInt('id_ce');
        $this->verifDroitOnConnecteur($id_ce);
        $password = $this->getPostInfo()->get('password');
        $password_check = $this->getPostInfo()->get('password_check');

        if ($password !== $password_check) {
            $this->setLastError('Les mots de passe ne correspondent pas.');
            $this->redirect("/Connecteur/export?id_ce=" . $id_ce);
        } elseif (mb_strlen($password) < Crypto::PASSWORD_MINIMUM_LENGTH) {
            $this->setLastError('Le mot de passe fait moins de ' . Crypto::PASSWORD_MINIMUM_LENGTH . ' caractères.');
            $this->redirect("/Connecteur/export?id_ce=" . $id_ce);
        }

        try {
            $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
        } catch (Exception $e) {
            $this->setLastError("Export impossible : Impossible de trouver la définition de ce connecteur");
            $this->redirect("/Connecteur/edition?id_ce=$id_ce");
        }

        $encryptedConnector = $this->getInstance(Crypto::class)
            ->encrypt($connecteurConfig->jsonExport(), $password);

        $connecteurEntite = $this->getConnecteurEntiteSQL();
        $info = $connecteurEntite->getInfo($id_ce);


        $filename = strtr($info['libelle'], " ", "_") . ".json";

        $this->getInstance(SendFileToBrowser::class)
            ->sendData($encryptedConnector, $filename, 'application/json');
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

        $this->{'page_title'} = "Importer des données pour le connecteur 
            « {$this->{'connecteur_entite_info'}['libelle']} »";
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
        $password = $this->getPostInfo()->get('password');

        $this->verifDroitOnConnecteur($id_ce);
        $fileUploader = new FileUploader();
        $file_content = $fileUploader->getFileContent('pser');

        $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
        try {
            $connecteurConfig->jsonImport($file_content);
        } catch (DonneesFormulaireException $exception) {
            try {
                $message = $this->getInstance(Crypto::class)->decrypt($file_content, $password);
                $connecteurConfig->jsonImport($message);
            } catch (Exception $e) {
                $this->setLastError($e->getMessage());
                $this->redirect("/Connecteur/import?id_ce=$id_ce");
            }
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/Connecteur/import?id_ce=$id_ce");
        }

        $message = "Les données du connecteur ont été importées";
        $this->getConnecteurActionService()->add(
            $this->getConnecteurEntiteSQL()->getInfo($id_ce)['id_e'],
            $this->getId_u(),
            $id_ce,
            '',
            ConnecteurActionService::ACTION_MODIFFIE,
            $message
        );
        $this->setLastMessage($message);

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
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function externalDataAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        $field = $recuperateur->get('field');

        $connecteur_info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        $id_e = $connecteur_info['id_e'];

        $this->verifDroit($id_e, "entite:edition", "/Connecteur/editionModif?id_ce=$id_ce");

        $documentType = $this->getDocumentTypeFactory()->getDocumentType($id_e, $connecteur_info['id_connecteur']);

        $formulaire = $documentType->getFormulaire();
        $formField = $formulaire->getField($field);
        if (!$formField) {
            $this->setLastError("Le champ $field n'existe pas");
            $this->redirect("/Connecteur/editionModif?id_ce=$id_ce");
        }

        $action_name = $formField->getProperties('choice-action');
        $result = $this->getActionExecutorFactory()->displayChoiceOnConnecteur(
            $id_ce,
            $this->getId_u(),
            $action_name,
            $field
        );
        if (!$result) {
            $this->setLastError($this->getActionExecutorFactory()->getLastMessage());
            $this->redirect("/Connecteur/editionModif?id_ce=$id_ce");
        }
    }

    private function getExternalDataActionName(int $entityId, string $field): string
    {
        $connecteur_info = $this->getConnecteurEntiteSQL()->getInfo($entityId);
        $id_e = $connecteur_info['id_e'];

        $this->verifDroit($id_e, "entite:edition", "/Connecteur/edition?id_ce=$entityId");

        $documentType = $this->getDocumentTypeFactory()->getDocumentType($id_e, $connecteur_info['id_connecteur']);
        $formulaire = $documentType->getFormulaire();
        $theField = $formulaire->getField($field);

        return $theField->getProperties('choice-action');
    }

    /**
     * @throws Exception
     */
    public function doExternalDataAction()
    {
        $recuperateur = $this->getPostOrGetInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        $field = $recuperateur->get('field');

        $action_name = $this->getExternalDataActionName($id_ce, $field);
        if (!$this->getActionExecutorFactory()->goChoiceOnConnecteur($id_ce, $this->getId_u(), $action_name, $field)) {
            $this->setLastError($this->getActionExecutorFactory()->getLastMessage());
        }
    }

    public function doExternalDataApiAction()
    {
        $recuperateur = $this->getPostOrGetInfo();
        $id_ce = $recuperateur->getInt('id_ce');
        $field = $recuperateur->get('field');

        $action_name = $this->getExternalDataActionName($id_ce, $field);
        $this->getActionExecutorFactory()->goChoiceOnConnecteur($id_ce, $this->getId_u(), $action_name, $field, true);
    }
}
