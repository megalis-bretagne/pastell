<?php

use Pastell\Service\Connecteur\MissingConnecteurService;
use Pastell\Service\Pack\PackService;
use Pastell\System\HealthCheck;

class SystemControler extends PastellControler
{

    public function _beforeAction()
    {
        parent::_beforeAction();
        $this->{'menu_gauche_template'} = "ConfigurationMenuGauche";
        $this->verifDroit(0, "system:lecture");
        $this->{'dont_display_breacrumbs'} = true;
    }

    /**
     * @throws NotFoundException
     */
    public function indexAction()
    {
        $this->verifDroit(0, "system:lecture");

        $this->{'droitEdition'} = $this->hasDroit(0, "system:edition");

        /** @var HealthCheck $healthCheck */
        $healthCheck = $this->getInstance(HealthCheck::class);
        $this->{'checkPhpExtensions'} = $healthCheck->checkPhpExtensions();
        $this->{'checkWorkspace'} = $healthCheck->checkWorkspace();
        $this->{'checkJournal'} = $healthCheck->checkJournal();
        $this->{'checkRedis'} = $healthCheck->checkRedis();
        $this->{'checkPhpConfiguration'} = $healthCheck->checkPhpConfiguration();
        $this->{'checkExpectedElements'} = $healthCheck->checkExpectedElements();
        $this->{'checkCommands'} = $healthCheck->checkCommands();
        $this->{'checkConstants'} = $healthCheck->getConstants();
        $this->{'checkDatabaseSchema'} = $healthCheck->checkDatabaseSchema();
        $this->{'checkDatabaseEncoding'} = $healthCheck->checkDatabaseEncoding();
        $this->{'checkCrashedTables'} = $healthCheck->checkCrashedTables();
        $this->{'checkMissingConnectors'} = $healthCheck->checkMissingConnectors();
        $this->{'checkMissingModules'} = $healthCheck->checkMissingModules();

        $packService = $this->getInstance(PackService::class);
        $this->{'listPack'} = $packService->getListPack();

        $this->{'manifest_info'} = $this->getManifestFactory()->getPastellManifest()->getInfo();

        $this->{'template_milieu'} = "SystemEnvironnement";
        $this->{'page_title'} = "Test du système";
        $this->{'menu_gauche_select'} = "System/index";
        $this->renderDefault();
    }

    public function getPageNumber($page_name)
    {
        $tab_number = array("system" => 0,
                                "flux" => 1,
                                "definition" => 2,
                                "connecteurs" => 4);
        return $tab_number[$page_name];
    }

    private function getTypeDocumentManquant()
    {
        $result = array();
        $document_type_list = $this->getDocumentSQL()->getAllType();
        $module_list = $this->getDocumentTypeFactory()->clearRestrictedFlux($this->getExtensions()->getAllModule());
        foreach ($document_type_list as $document_type) {
            if (empty($module_list[$document_type])) {
                $result[] = $document_type;
            }
        }
        return $result;
    }

    public function fluxAction()
    {
        $all_flux = array();
        $all_flux_restricted = array();

        $documentTypeValidation = $this->getDocumentTypeValidation();
        foreach ($this->getFluxDefinitionFiles()->getAll() as $id_flux => $flux) {
            $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id_flux);
            $all_flux[$id_flux]['nom'] = $documentType->getName();
            $all_flux[$id_flux]['type'] = $documentType->getType();
            $all_flux[$id_flux]['list_restriction_pack'] = $documentType->getListRestrictionPack();
            $definition_path = $this->getFluxDefinitionFiles()->getDefinitionPath($id_flux);
            $all_flux[$id_flux]['is_valide'] = $documentTypeValidation->validate($definition_path);
        }
        $this->{'all_flux'} = $all_flux;

        foreach ($this->getFluxDefinitionFiles()->getAllRestricted() as $id_flux) {
            $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id_flux);
            $all_flux_restricted[$id_flux]['nom'] = $documentType->getName();
            $all_flux_restricted[$id_flux]['type'] = $documentType->getType();
            $all_flux_restricted[$id_flux]['list_restriction_pack'] = $documentType->getListRestrictionPack();
        }
        $this->{'all_flux_restricted'} = $all_flux_restricted;

        $this->{'template_milieu'} = "SystemFlux";
        $this->{'page_title'} = "Types de dossier disponibles sur la plateforme";
        $this->{'menu_gauche_select'} = "System/flux";
        $this->renderDefault();
    }


    private function getDocumentTypeValidation()
    {
        /** @var ActionExecutorFactory $actionExecutorFactory */
        $actionExecutorFactory = $this->{'ActionExecutorFactory'};
        $all_action_class = $actionExecutorFactory->getAllActionClass();

        /** @var PackService $packService */
        $list_pack = $this->getInstance(PackService::class)->getListPack();
        $all_connecteur_type = $this->getConnecteurDefinitionFiles()->getAllType();
        $all_type_entite = array_keys(Entite::getAllType());


        $connecteur_type_action_class_list = $this->{'ConnecteurTypeFactory'}->getAllActionExecutor();

        /** @var DocumentTypeValidation $documentTypeValidation */
        $documentTypeValidation = $this->{'DocumentTypeValidation'};
        $documentTypeValidation->setListPack($list_pack);
        $documentTypeValidation->setConnecteurTypeList($all_connecteur_type);
        $documentTypeValidation->setEntiteTypeList($all_type_entite);
        $documentTypeValidation->setActionClassList($all_action_class);
        $documentTypeValidation->setConnecteurTypeActionClassList($connecteur_type_action_class_list);
        return $documentTypeValidation;
    }

    private function getAllActionInfo(DocumentType $documentType, $type = 'flux')
    {
        $id = $documentType->getModuleId();
        $all_action = array();
        $action = $documentType->getAction();
        $action_list = $action->getAll();
        sort($action_list);
        foreach ($action_list as $action_name) {
            $class_name = $action->getActionClass($action_name);
            $element = array(
                'id' => $action_name,
                'name' => $action->getActionName($action_name),
                'do_name' => $action->getDoActionName($action_name),
                'class' => $class_name,

                'action_auto' => $action->getActionAutomatique($action_name)
            );

            if ($type == 'connecteur') {
                $element['path'] = $this->getActionExecutorFactory()->getConnecteurActionPath($id, $class_name);
            } else {
                $element['path'] = $this->getActionExecutorFactory()->getFluxActionPath($id, $class_name);
            }

            $all_action[] = $element;
        }
        return $all_action;
    }

    private function getFormsElement(DocumentType $documentType)
    {
        $formulaire = $documentType->getFormulaire();

        $allFields = $formulaire->getAllFields();
        $form_fields = array();
        /** @var Field $field */
        foreach ($allFields as $field) {
            $form_fields[$field->getName()] = $field->getAllProperties();
        }
        return $form_fields;
    }

    /**
     * @throws NotFoundException
     */
    public function fluxDetailAction()
    {
        $id = $this->getGetInfo()->get('id');
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id);
        $name = $documentType->getName();
        $this->{'description'} = $documentType->getDescription();
        $this->{'list_restriction_pack'} = $documentType->getListRestrictionPack();
        $this->{'all_connecteur'} = $documentType->getConnecteur();

        $this->{'all_action'} = $this->getAllActionInfo($documentType);

        $this->{'formulaire_fields'} = $this->getFormsElement($documentType);

        $document_type_is_validate = false;
        $validation_error = false;
        try {
            $document_type_is_validate = $this->isDocumentTypeValid($id);
        } catch (Exception $e) {
            $validation_error = $this->getDocumentTypeValidation()->getLastError();
        }

        $this->{'document_type_is_validate'} = $document_type_is_validate;
        $this->{'validation_error'} = $validation_error;

        $this->{'page_title'} = "Détail du type de dossier « $name » ($id)";
        $this->{'template_milieu'} = "SystemFluxDetail";
        $this->{'menu_gauche_select'} = "System/flux";

        $this->renderDefault();
    }

    public function definitionAction()
    {
        $this->{'flux_definition'} = $this->getDocumentTypeValidation()->getModuleDefinition();
        $this->{'page_title'} = "Définition des types de dossier";
        $this->{'template_milieu'} = "SystemFluxDef";
        $this->{'menu_gauche_select'} = "System/definition";
        $this->renderDefault();
    }

    public function connecteurAction()
    {
        $all_connecteur_globaux = [];
        $all_connecteur_globaux_restricted = [];
        $all_connecteur_entite = [];
        $all_connecteur_entite_restricted = [];

        foreach ($this->getConnecteurDefinitionFiles()->getAllGlobal() as $id_connecteur => $connecteur) {
            $documentType = $this->getDocumentTypeFactory()->getGlobalDocumentType($id_connecteur);
            $all_connecteur_globaux[$id_connecteur]['nom'] = $documentType->getName();
            $all_connecteur_globaux[$id_connecteur]['description'] = $documentType->getDescription();
            $all_connecteur_globaux[$id_connecteur]['list_restriction_pack'] = $documentType->getListRestrictionPack();
        }
        $this->{'all_connecteur_globaux'} = $all_connecteur_globaux;

        foreach ($this->getConnecteurDefinitionFiles()->getAll() as $id_connecteur => $connecteur) {
            $documentType = $this->getDocumentTypeFactory()->getEntiteDocumentType($id_connecteur);
            $all_connecteur_entite[$id_connecteur]['nom'] = $documentType->getName();
            $all_connecteur_entite[$id_connecteur]['description'] = $documentType->getDescription();
            $all_connecteur_entite[$id_connecteur]['list_restriction_pack'] = $documentType->getListRestrictionPack();
        }
        $this->{'all_connecteur_entite'} = $all_connecteur_entite;

        foreach ($this->getConnecteurDefinitionFiles()->getAllRestricted(true) as $id_connecteur) {
            $documentType = $this->getDocumentTypeFactory()->getGlobalDocumentType($id_connecteur);
            $all_connecteur_globaux_restricted[$id_connecteur]['nom'] = $documentType->getName();
            $all_connecteur_globaux_restricted[$id_connecteur]['list_restriction_pack'] = $documentType->getListRestrictionPack();
        }
        $this->{'all_connecteur_globaux_restricted'} = $all_connecteur_globaux_restricted;

        foreach ($this->getConnecteurDefinitionFiles()->getAllRestricted() as $id_connecteur) {
            $documentType = $this->getDocumentTypeFactory()->getEntiteDocumentType($id_connecteur);
            $all_connecteur_entite_restricted[$id_connecteur]['nom'] = $documentType->getName();
            $all_connecteur_entite_restricted[$id_connecteur]['list_restriction_pack'] = $documentType->getListRestrictionPack();
        }
        $this->{'all_connecteur_entite_restricted'} = $all_connecteur_entite_restricted;

        $this->{'page_title'} = "Connecteurs disponibles";
        $this->{'template_milieu'} = "SystemConnecteurList";
        $this->{'menu_gauche_select'} = "System/connecteur";
        $this->renderDefault();
    }


    /**
     * @param $id_flux
     * @return bool
     * @throws Exception
     */
    public function isDocumentTypeValid($id_flux)
    {
        $definition_path = $this->getFluxDefinitionFiles()->getDefinitionPath($id_flux);
        return $this->isDocumentTypeValidByDefinitionPath($definition_path);
    }

    /**
     * @param $definition_path
     * @return bool
     * @throws Exception
     */
    public function isDocumentTypeValidByDefinitionPath($definition_path)
    {
        $documentTypeValidation = $this->getDocumentTypeValidation();
        if (! $documentTypeValidation->validate($definition_path)) {
            throw new UnrecoverableException(implode("\n", $this->getDocumentTypeValidation()->getLastError())) ;
        }
        return true;
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws Exception
     */
    public function mailTestAction()
    {
        $this->verifDroit(0, "system:edition");

        $email = $this->getPostInfo()->get("email");
        if (! $email) {
            $this->setLastError("Merci de spécifier un email");
            $this->redirect('System/index');
        }

        $this->getZenMail()->setEmetteur("Pastell", PLATEFORME_MAIL);
        $this->getInstance("ZenMail")->setReturnPath(PLATEFORME_MAIL);

        $this->getZenMail()->setDestinataire($email);
        $this->getZenMail()->setSujet("[Pastell] Mail de test");

        $this->getZenMail()->resetAttachment();
        $this->getZenMail()->addAttachment(
            'test-pastell-i-parapheur.pdf',
            __DIR__ . '/../connecteur/iParapheur/data-exemple/test-pastell-i-parapheur.pdf'
        );

        $this->getZenMail()->setContenu(PASTELL_PATH . "/mail/test.php", array());
        $this->getZenMail()->send();

        $this->setLastMessage("Un email a été envoyé à l'adresse  : " . get_hecho($email));
        $this->redirect('System/index');
    }

    public function phpinfoAction()
    {
        $this->verifDroit(0, "system:edition");
        phpinfo();
        return;
    }

    /**
     * @throws Exception
     */
    public function connecteurDetailAction()
    {
        $this->verifDroit(0, "system:lecture");

        $id_connecteur = $this->getGetInfo()->get('id_connecteur');
        $scope = $this->getGetInfo()->get('scope');
        if ($scope == 'global') {
            $documentType = $this->getDocumentTypeFactory()->getGlobalDocumentType($id_connecteur);
        } else {
            $documentType = $this->getDocumentTypeFactory()->getEntiteDocumentType($id_connecteur);
        }
        $name = $documentType->getName();
        $this->{'description'} = $documentType->getDescription();
        $this->{'list_restriction_pack'} = $documentType->getListRestrictionPack();
        $this->{'all_action'} = $this->getAllActionInfo($documentType, 'connecteur');
        $this->{'formulaire_fields'} = $this->getFormsElement($documentType);

        $this->{'page_title'} = "Détail du connecteur " . ($scope == 'global' ? 'global' : "d'entité") . " « $name » ($id_connecteur)";
        $this->{'menu_gauche_select'} = "System/connecteur";
        $this->{'template_milieu'} = "SystemConnecteurDetail";
        $this->renderDefault();
    }

    public function sendWarningAction()
    {
        $this->getLogger()->warning("Warning emis par System/Warning");
        $this->setLastMessage("Un warning a été généré");
        $this->redirect('System/index');
    }

    public function sendFatalErrorAction()
    {
        trigger_error("Déclenchement manuel d'une erreur fatale !", E_USER_ERROR);
    }


    /**
     * @throws NotFoundException
     */
    public function loginPageConfigurationAction()
    {
        $this->verifDroit(0, 'system:lecture');

        $this->{'login_page_configuration'} = file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION)
            ? file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION)
            : '';
        $this->{'page_title'} = '';
        $this->{'menu_gauche_select'} = 'System/loginPageConfiguration';
        $this->{'template_milieu'} = 'LoginPageConfiguration';
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doLoginPageConfigurationAction()
    {
        $this->verifDroit(0, 'system:edition');

        $result = file_put_contents(
            LOGIN_PAGE_CONFIGURATION_LOCATION,
            $this->getPostInfo()->get(LOGIN_PAGE_CONFIGURATION)
        );

        if ($result === false) {
            $this->setLastError("Impossible d'enregistrer la configuration de la page de connexion");
        } else {
            $this->setLastMessage('La configuration de la page de connexion a été enregistrée');
        }

        $this->redirect('System/loginPageConfiguration');
    }

    /**
     * @throws NotFoundException
     */
    public function missingConnecteurAction()
    {
        $this->{'page_title'} = 'Connecteurs manquants';
        $this->{'template_milieu'} = 'SystemMissingConnecteur';
        $this->{'menu_gauche_select'} = "System/index";

        $this->{'connecteur_manquant_list'} = $this->getConnecteurFactory()->getManquant();

        $this->renderDefault();
    }

    /**
     * @throws Exception
     */
    public function exportAllMissingConnecteurAction()
    {
        $this->verifDroit(0, "system:edition");
        $tmpFoder  = new TmpFolder();
        $tmp_folder = $tmpFoder->create();
        $zip_filepath = "$tmp_folder/pastell-all-missing-connecteur.zip";
        $this->getObjectInstancier()->getInstance(MissingConnecteurService::class)->exportAll($zip_filepath);
        $sendFileToBrowser = $this->getObjectInstancier()->getInstance(SendFileToBrowser::class);
        $sendFileToBrowser->sendData(
            file_get_contents($zip_filepath),
            "pastell-all-missing-connecteur.zip",
            "application/zip"
        );
        $tmpFoder->delete($tmp_folder);
    }
}
