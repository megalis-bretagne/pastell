<?php

use Pastell\Service\TypeDossier\TypeDossierDeletionService;
use Pastell\Service\TypeDossier\TypeDossierEditionService;
use Pastell\Service\TypeDossier\TypeDossierExportService;
use Pastell\Service\TypeDossier\TypeDossierImportService;
use Pastell\Service\TypeDossier\TypeDossierUtilService;
use Pastell\Service\TypeDossier\TypeDossierManager;
use Pastell\Service\TypeDossier\TypeDossierActionService;

class TypeDossierControler extends PastellControler
{
    public function _beforeAction()
    {
        parent::_beforeAction();
        $this->{'menu_gauche_template'} = "ConfigurationMenuGauche";
        $this->{'menu_gauche_select'} = "TypeDossier/list";
        $this->verifDroit(0, "system:lecture");
        $this->{'dont_display_breacrumbs'} = true;
    }

    private function commonEdition()
    {
        $this->verifDroit(0, "system:edition");
        $this->{'id_t'} = $this->getPostOrGetInfo()->getInt('id_t');
        $this->{'type_de_dossier_info'} = $this->getTypeDossierSQL()->getInfo($this->{'id_t'});
        $this->{'type_dossier_hash'} = $this->getTypeDossierManager()->getHash($this->{'id_t'});
        $this->{'typeDossierProperties'} = $this->getTypeDossierManager()->getTypeDossierProperties($this->{'id_t'});
        $this->{'page_title'} = "Type de dossier personnalisé {$this->{'type_de_dossier_info'}['id_type_dossier']}";
        $this->{'id_type_dossier'} = $this->{'type_de_dossier_info'}['id_type_dossier'];

        $typeDossierEtape = $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);
        $this->{'all_etape_type'} = $typeDossierEtape->getAllType();
    }

    /**
     * @return TypeDossierSQL
     */
    private function getTypeDossierSQL()
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
    }

    /**
     * @return TypeDossierService
     */
    private function getTypeDossierService()
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierService::class);
    }

    /**
     * @return TypeDossierManager
     */
    private function getTypeDossierManager()
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierManager::class);
    }

    /**
     * @return TypeDossierEditionService
     */
    private function getTypeDossierEditionService()
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierEditionService::class);
    }

    /**
     * @return TypeDossierEtapeManager
     */
    private function getTypeDossierEtapeDefinition()
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);
    }

    /**
     * @return TypeDossierActionService
     */
    private function getTypeDossierActionService(): TypeDossierActionService
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierActionService::class);
    }

    /**
     * @throws NotFoundException
     */
    public function listAction()
    {
        $this->{'type_dossier_list'} = $this->getTypeDossierSQL()->getAll();
        $this->{'droit_edition'} = $this->hasDroit(0, "system:edition");
        $this->{'page_title'} = "Types de dossier personnalisés";
        $this->{'menu_gauche_select'} = "TypeDossier/list";
        $this->{'template_milieu'} = "TypeDossierList";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function editionAction()
    {
        $this->verifDroit(0, "system:edition");
        $id_t = $this->getPostOrGetInfo()->getInt('id_t');
        $this->{'flux_info'} = $this->getTypeDossierSQL()->getInfo($id_t);

        if ($this->{'flux_info'}['id_type_dossier']) {
            $id_type_dossier = $this->{'flux_info'}['id_type_dossier'];

            if ($this->getDocumentSQL()->isTypePresent($id_type_dossier)) {
                $this->setLastError(
                    "Des dossiers du type <b>$id_type_dossier</b> existent déjà sur ce Pastell. Impossible de modifier l'identifiant."
                );
                $this->redirect("/TypeDossier/list");
            }
        }

        $this->{'page_title'} = "Création d'un type de dossier personnalisé";
        $this->{'menu_gauche_select'} = "TypeDossier/list";
        $this->{'template_milieu'} = "TypeDossierEdition";
        $this->renderDefault();
    }

    /**
     * @throws Exception
     */
    public function doEditionAction()
    {
        $this->verifDroit(0, "system:edition");

        $id_t = $this->getPostOrGetInfo()->getInt('id_t');
        $is_new = ! $id_t;

        $target_type_dossier_id = $this->getPostOrGetInfo()->get('id_type_dossier');
        $typeDossierProperties = $this->getTypeDossierManager()->getTypeDossierProperties($id_t);
        $source_type_dossier_id = $typeDossierProperties->id_type_dossier;

        $this->verifyTypeDossierIsUnused($source_type_dossier_id);

        $typeDossierEditionService = $this->getTypeDossierEditionService();
        try {
            $typeDossierEditionService->checkTypeDossierId($target_type_dossier_id);
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/TypeDossier/list");
        }

        if (! $is_new) {
            $typeDossierEditionService->renameTypeDossierId($source_type_dossier_id, $target_type_dossier_id);
        }
        $typeDossierProperties->id_type_dossier = $target_type_dossier_id;
        try {
            $id_t = $typeDossierEditionService->edit($id_t, $typeDossierProperties);
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/TypeDossier/list");
        }

        if ($is_new) {
            $typeDossierEditionService->editLibelleInfo($id_t, $target_type_dossier_id, TypeDossierUtilService::TYPE_DOSSIER_CLASSEMENT_DEFAULT, "", "onglet1");
            $message = "Le type de dossier personnalisé $target_type_dossier_id a été créé";
            $this->getTypeDossierActionService()->add($this->getId_u(), $id_t, TypeDossierActionService::ACTION_AJOUTE, $message);
        } else {
            $message = "Modification de l'identifiant du type de dossier personnalisé $target_type_dossier_id";
            $this->getTypeDossierActionService()->add($this->getId_u(), $id_t, TypeDossierActionService::ACTION_MODIFFIE, $message);
        }
        $this->setLastMessage($message);

        $this->redirect("/TypeDossier/detail?id_t=$id_t");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function deleteAction()
    {
        $this->commonEdition();

        $id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyTypeDossierIsUnused($id_type_dossier);

        $this->{'template_milieu'} = "TypeDossierDelete";
        $this->renderDefault();
    }

    /**
     * @param $id_type_dossier
     * @throws LastErrorException
     * @throws LastMessageException
     */
    private function verifyTypeDossierIsUnused($id_type_dossier)
    {
        $this->verifyNoDocumentIsUsingTypeDossier($id_type_dossier);
        $this->verifyNoRoleIsUsingTypeDossier($id_type_dossier);
        $this->verifyNoConnectorIsAssociatedToTypeDossier($id_type_dossier);
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws TypeDossierException
     */
    public function doDeleteAction()
    {
        $this->commonEdition();
        $id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyTypeDossierIsUnused($id_type_dossier);

        $this->getObjectInstancier()->getInstance(TypeDossierDeletionService::class)->delete($this->{'id_t'});

        $this->setLastMessage("Le type de dossier <b>{$this->{'id_type_dossier'}}</b> a été supprimé");
        $this->redirect("/TypeDossier/list");
    }

    /**
     * @throws NotFoundException
     */
    public function detailAction()
    {
        $this->commonEdition();
        $this->{'offset'} = $this->getPostOrGetInfo()->get('offset', 0);
        $this->{'limit'} = 20;
        $this->{'count'} = $this->getTypeDossierActionService()->countById($this->{'id_t'});
        $this->{'csrfToken'} = $this->getObjectInstancier()->getInstance(CSRFToken::class);

        $this->{'template_milieu'} = "TypeDossierDetail";
        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     */
    public function etatAction()
    {
        $this->verifDroit(0, "system:edition");
        $this->{'id_t'} = $this->getPostOrGetInfo()->getInt('id_t');
        $this->{'page_title'} = "États du type de dossier personnalisé {$this->getTypeDossierSQL()->getInfo($this->{'id_t'})['id_type_dossier']}";
        $this->{'offset'} = $this->getPostOrGetInfo()->get('offset', 0);
        $this->{'limit'} = 10;
        $this->{'count'} = $this->getTypeDossierActionService()->countById($this->{'id_t'});
        $this->{'typeDossierAction'} = $this->getTypeDossierActionService()->getById($this->{'id_t'}, $this->{'offset'}, $this->{'limit'});

        $this->{'template_milieu'} = "TypeDossierEtat";
        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     */
    public function editionLibelleAction()
    {
        $this->commonEdition();
        $this->{'template_milieu'} = "TypeDossierEditionLibelle";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doEditionLibelleAction()
    {
        $this->commonEdition();
        $nom = $this->getPostOrGetInfo()->get('nom');
        $type = $this->getPostOrGetInfo()->get('type');
        $description = $this->getPostOrGetInfo()->get('description');
        $nom_onglet = $this->getPostOrGetInfo()->get('nom_onglet');
        try {
            $this->getTypeDossierEditionService()->editLibelleInfo($this->{'id_t'}, $nom, $type, $description, $nom_onglet);
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/TypeDossier/editionLibelle?id_t={$this->{'id_t'}}");
        }
        $message = "La modification des informations sur le type de dossier a été enregistrée";
        $this->getTypeDossierActionService()->add($this->getId_u(), $this->{'id_t'}, TypeDossierActionService::ACTION_MODIFFIE, $message);
        $this->setLastMessage($message);
        $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
    }

    /**
     * @throws NotFoundException
     */
    public function editionElementAction()
    {
        $this->commonEdition();
        $element_id = $this->getPostOrGetInfo()->get('element_id');
        $this->{'formulaireElement'} = $this->getTypeDossierService()->getFormulaireElement($this->{'id_t'}, $element_id);
        $this->{'template_milieu'} = "TypeDossierEditionElement";
        $this->renderDefault();
    }

    /**
     * @throws Exception
     */
    public function doEditionElementAction()
    {
        $this->commonEdition();
        $id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyNoDocumentIsUsingTypeDossier($id_type_dossier, '/TypeDossier/detail?id_t=' . $this->{'id_t'});
        try {
            $this->getTypeDossierService()->editionElement($this->{'id_t'}, $this->getPostOrGetInfo());
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
        }
        $message = "La modification d'éléments du formulaire a été enregistrée";
        $this->getTypeDossierActionService()->add($this->getId_u(), $this->{'id_t'}, TypeDossierActionService::ACTION_MODIFFIE, $message);
        $this->setLastMessage($message);
        $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
    }


    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function deleteElementAction()
    {
        $this->commonEdition();
        $id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyNoDocumentIsUsingTypeDossier($id_type_dossier, '/TypeDossier/detail?id_t=' . $this->{'id_t'});
        $element_id = $this->getPostOrGetInfo()->get('element_id');
        try {
            $this->getTypeDossierService()->deleteElement($this->{'id_t'}, $element_id);
        } catch (Exception $e) {
            $this->setLastMessage($e->getMessage());
            $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
        }
        $message = "La modification d'éléments du formulaire a été enregistrée";
        $this->getTypeDossierActionService()->add($this->getId_u(), $this->{'id_t'}, TypeDossierActionService::ACTION_MODIFFIE, $message);
        $this->setLastMessage($message);
        $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
    }

    /**
     * @throws NotFoundException
     */
    public function editionEtapeAction()
    {
        $this->commonEdition();
        $num_etape = $this->getPostOrGetInfo()->get('num_etape', 0);

        $this->{'file_field_list'} = $this->getTypeDossierService()->getFieldWithType($this->{'id_t'}, 'file');
        $this->{'multi_file_field_list'} = $this->getTypeDossierService()->getFieldWithType($this->{'id_t'}, 'multi_file');
        $this->{'text_field_list'} = $this->getTypeDossierService()->getFieldWithType($this->{'id_t'}, 'text');

        $this->{'etapeInfo'} = $this->getTypeDossierService()->getEtapeInfo($this->{'id_t'}, $num_etape);
        $this->{'formulaire_etape'} = $this->getTypeDossierEtapeDefinition()->getFormulaireConfigurationEtape($this->{'etapeInfo'}->type);

        $this->{'template_milieu'} = "TypeDossierEditionEtape";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doEditionEtapeAction()
    {
        $this->commonEdition();
        $id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyNoDocumentIsUsingTypeDossier($id_type_dossier, '/TypeDossier/detail?id_t=' . $this->{'id_t'});
        try {
            $this->getTypeDossierService()->editionEtape($this->{'id_t'}, $this->getPostOrGetInfo());
        } catch (Exception $e) {
            $this->setLastMessage($e->getMessage());
            $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
        }
        $message = "La modification des étapes du cheminement a été enregistrée";
        $this->getTypeDossierActionService()->add($this->getId_u(), $this->{'id_t'}, TypeDossierActionService::ACTION_MODIFFIE, $message);
        $this->setLastMessage($message);
        $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function deleteEtapeAction()
    {
        $this->commonEdition();
        $id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyNoDocumentIsUsingTypeDossier($id_type_dossier, '/TypeDossier/detail?id_t=' . $this->{'id_t'});
        $num_etape = $this->getPostOrGetInfo()->getInt('num_etape');
        try {
            $this->getTypeDossierService()->deleteEtape($this->{'id_t'}, $num_etape);
        } catch (Exception $e) {
            $this->setLastMessage($e->getMessage());
            $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
        }
        $message = "La modification des étapes du cheminement a été enregistrée";
        $this->getTypeDossierActionService()->add($this->getId_u(), $this->{'id_t'}, TypeDossierActionService::ACTION_MODIFFIE, $message);
        $this->setLastMessage($message);
        $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
    }

    /**
     * @throws Exception
     */
    public function sortElementAction()
    {
        $this->commonEdition();
        $tr = $this->getPostInfo()->get("tr");
        $this->getTypeDossierService()->sortElement($this->{'id_t'}, $tr);
        $message = "L'ordre des éléments du formulaire a été modifié";
        $this->getTypeDossierActionService()->add($this->getId_u(), $this->{'id_t'}, TypeDossierActionService::ACTION_MODIFFIE, $message);
        print_r($tr);
        echo "OK";
    }

    /**
     * @throws Exception
     */
    public function sortEtapeAction()
    {
        $this->commonEdition();
        $id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyNoDocumentIsUsingTypeDossier($id_type_dossier);
        $tr = $this->getPostInfo()->get("tr");
        $this->getTypeDossierService()->sortEtape($this->{'id_t'}, $tr);
        $message = "L'ordre des étapes du cheminement a été modifié";
        $this->getTypeDossierActionService()->add($this->getId_u(), $this->{'id_t'}, TypeDossierActionService::ACTION_MODIFFIE, $message);

        print_r($tr);
        echo "OK";
    }

    /**
     * @throws NotFoundException
     */
    public function newEtapeAction()
    {
        $this->commonEdition();
        $this->{'template_milieu'} = "TypeDossierNewEtape";
        $this->{'etapeInfo'} = $this->getTypeDossierService()->getEtapeInfo($this->{'id_t'}, "new");
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doNewEtapeAction()
    {
        $this->commonEdition();
        $id_type_dossier = $this->{'type_de_dossier_info'}['id_type_dossier'];
        $this->verifyNoDocumentIsUsingTypeDossier($id_type_dossier, '/TypeDossier/detail?id_t=' . $this->{'id_t'});
        $num_etape = 0;
        try {
            $num_etape = $this->getTypeDossierService()->newEtape($this->{'id_t'}, $this->getPostOrGetInfo());
        } catch (Exception $e) {
            $this->setLastMessage($e->getMessage());
            $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
        }

        $etapeInfo = $this->getTypeDossierService()->getEtapeInfo($this->{'id_t'}, $num_etape);
        $message = "La modification des étapes du cheminement a été enregistrée";
        if ($etapeInfo->specific_type_info) {
            $this->getTypeDossierActionService()->add($this->getId_u(), $this->{'id_t'}, TypeDossierActionService::ACTION_MODIFFIE, $message);
            $this->setLastMessage("L'étape a été créée. Veuillez saisir les propriétés spécifiques de l'étape.");
            $this->redirect("/TypeDossier/editionEtape?id_t={$this->{'id_t'}}&num_etape=$num_etape");
        }
        $this->getTypeDossierActionService()->add($this->getId_u(), $this->{'id_t'}, TypeDossierActionService::ACTION_MODIFFIE, $message);
        $this->setLastMessage("L'étape a été créée.");
        $this->redirect("/TypeDossier/detail?id_t={$this->{'id_t'}}");
    }

    public function exportAction()
    {
        $id_t = $this->getPostOrGetInfo()->getInt('id_t');
        $type_dossier_info = $this->getTypeDossierSQL()->getInfo($id_t);
        $typeDossierExportService = $this->getObjectInstancier()->getInstance(TypeDossierExportService::class);
        $data_to_send = $typeDossierExportService->export($id_t);
        $sendFileToBrowser = $this->getObjectInstancier()->getInstance(SendFileToBrowser::class);
        $sendFileToBrowser->sendData($data_to_send, $type_dossier_info['id_type_dossier'] . ".json", "application/json");
    }

    /**
     * @throws NotFoundException
     */
    public function importAction()
    {
        $this->verifDroit(0, "system:edition");
        $this->{'page_title'} = "Import d'un type de dossier personnalisé";
        $this->{'menu_gauche_select'} = "TypeDossier/list";
        $this->{'template_milieu'} = "TypeDossierImport";
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doImportAction()
    {
        $this->verifDroit(0, "system:edition");
        $fileUploader  = $this->getObjectInstancier()->getInstance(FileUploader::class);
        $file_content = $fileUploader->getFileContent("json_type_dossier");

        $typeDossierImportService = $this->getObjectInstancier()->getInstance(TypeDossierImportService::class);

        $result = [];
        try {
            $result = $typeDossierImportService->import($file_content);
        } catch (TypeDossierException $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/TypeDossier/import");
        }

        if ($result[TypeDossierUtilService::ID_TYPE_DOSSIER] == $result[TypeDossierUtilService::ORIG_ID_TYPE_DOSSIER]) {
            $message = "Le type de dossier  <b>{$result['id_type_dossier']}</b> a été importé.";
        } else {
            $message = "Le type de dossier a été importé avec l'identifiant <b>{$result[TypeDossierUtilService::ID_TYPE_DOSSIER]}</b> car l'identiant original ({$result[TypeDossierUtilService::ORIG_ID_TYPE_DOSSIER]}) existe déjà sur la plateforme";
        }
        $this->getTypeDossierActionService()->add($this->getId_u(), $result[TypeDossierUtilService::ID_T], TypeDossierActionService::ACTION_AJOUTE, $message);
        $this->setLastMessage($message);
        $this->redirect("/TypeDossier/detail?id_t={$result['id_t']}");
    }

    /**
     * @param $id_type_dossier
     * @throws LastErrorException
     * @throws LastMessageException
     */
    private function verifyNoRoleIsUsingTypeDossier($id_type_dossier): void
    {
        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);

        $role_list = array_unique(array_merge(
            $roleSQL->getRoleByDroit("$id_type_dossier:lecture"),
            $roleSQL->getRoleByDroit("$id_type_dossier:edition")
        ));

        if ($role_list) {
            if (count($role_list) == 1) {
                $this->setLastError(
                    "Le type de dossier <b>{$id_type_dossier}</b> est utilisé par le rôle « {$role_list[0]} »"
                );
            } else {
                $this->setLastError(
                    "Le type de dossier <b>{$id_type_dossier}</b> est utilisé par les rôles suivants " . implode(",", $role_list)
                );
            }
            $this->redirect("/TypeDossier/list");
        }
    }

    /**
     * @param $id_type_dossier
     * @throws LastErrorException
     * @throws LastMessageException
     */
    private function verifyNoConnectorIsAssociatedToTypeDossier($id_type_dossier): void
    {
        $fluxEntiteSQL = $this->getObjectInstancier()->getInstance(FluxEntiteSQL::class);
        $entite_list = $fluxEntiteSQL->getEntiteByFlux($id_type_dossier);
        if ($entite_list) {
            $output = [];
            foreach ($entite_list as $entite_info) {
                $output[] = "{$entite_info['denomination']} (id_e={$entite_info['id_e']})";
            }
            if (count($output) == 1) {
                $message = "Le type de dossier <b>{$id_type_dossier}</b> a été associé avec des connecteurs sur l'entité ";
            } else {
                $message = "Le type de dossier <b>{$id_type_dossier}</b> a été associé avec des connecteurs sur les entités : ";
            }
            $this->setLastError(
                $message . implode(", ", $output)
            );
            $this->redirect("/TypeDossier/list");
        }
    }

    /**
     * @param $id_type_dossier
     * @param string $redirectTo
     * @throws LastErrorException
     * @throws LastMessageException
     */
    private function verifyNoDocumentIsUsingTypeDossier($id_type_dossier, $redirectTo = '/TypeDossier/list'): void
    {
        $entite_list = $this->getDocumentSQL()->getEntiteWhichUsedDocument($id_type_dossier);

        if (! $entite_list) {
            return;
        }
        $gabarit = $this->getObjectInstancier()->getInstance(Gabarit::class);
        $gabarit->setParameters([
                'entite_list' => $entite_list,
                'id_type_dossier' => $id_type_dossier
        ]);
        $content = $gabarit->getRender("TypeDossierCountByEntiteBox");

        $this->setLastError(
            "La modification n'est pas possible. Le type de dossier {$id_type_dossier} est utilisé par des dossiers qui ne sont pas dans l'état <i>terminé</i> ou <i>erreur fatale</i>: $content<br/>"
        );
        $this->redirect($redirectTo);
    }
}
