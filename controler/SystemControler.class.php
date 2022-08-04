<?php

use Pastell\Mailer\Mailer;
use Pastell\Service\Connecteur\MissingConnecteurService;
use Pastell\Service\Droit\DroitService;
use Pastell\Service\FeatureToggle\DisplayFeatureToggleInTestPage;
use Pastell\Service\FeatureToggleService;
use Pastell\Service\Pack\PackService;
use Pastell\System\HealthCheck;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;

class SystemControler extends PastellControler
{
    private const SYSTEM_INDEX_PAGE = "System/index";

    public function _beforeAction()
    {
        parent::_beforeAction();
        $this->setViewParameter('menu_gauche_template', "ConfigurationMenuGauche");
        $this->verifDroit(0, DroitService::getDroitLecture(DroitService::DROIT_SYSTEM));
        $this->setViewParameter('dont_display_breacrumbs', true);
    }

    private function needDroitEdition()
    {
        $this->verifDroit(0, DroitService::getDroitEdition(DroitService::DROIT_SYSTEM));
    }

    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function indexAction()
    {
        $this->setViewParameter('droitEdition', $this->hasDroit(0, DroitService::getDroitEdition(DroitService::DROIT_SYSTEM)));

        /** @var HealthCheck $healthCheck */
        $healthCheck = $this->getInstance(HealthCheck::class);
        $this->setViewParameter('checkWorkspace', $healthCheck->check(HealthCheck::WORKSPACE_CHECK));
        $this->setViewParameter('checkJournal', $healthCheck->check(HealthCheck::JOURNAL_CHECK));
        $this->setViewParameter('checkRedis', $healthCheck->check(HealthCheck::REDIS_CHECK));
        $this->setViewParameter('checkPhpConfiguration', $healthCheck->check(HealthCheck::PHP_CONFIGURATION_CHECK));
        $this->setViewParameter('checkPhpExtensions', $healthCheck->check(HealthCheck::PHP_EXTENSIONS_CHECK));
        $this->setViewParameter('checkExpectedElements', $healthCheck->check(HealthCheck::EXPECTED_ELEMENTS_CHECK));
        $this->setViewParameter('checkCommands', $healthCheck->check(HealthCheck::COMMAND_CHECK));
        $this->setViewParameter('checkConstants', $healthCheck->check(HealthCheck::CONSTANTS_CHECK));
        $this->setViewParameter('checkDatabaseSchema', $healthCheck->check(HealthCheck::DATABASE_SCHEMA_CHECK)[0]);
        $this->setViewParameter('checkDatabaseEncoding', $healthCheck->check(HealthCheck::DATABASE_ENCODING_CHECK)[0]);
        $this->setViewParameter('checkCrashedTables', $healthCheck->check(HealthCheck::CRASHED_TABLES_CHECK)[0]);
        $this->setViewParameter('checkMissingConnectors', $healthCheck->check(HealthCheck::MISSING_CONNECTORS_CHECK)[0]);
        $this->setViewParameter('checkMissingModules', $healthCheck->check(HealthCheck::MISSING_MODULES_CHECK)[0]);

        $packService = $this->getInstance(PackService::class);
        $this->setViewParameter('listPack', $packService->getListPack());

        $this->setViewParameter('manifest_info', $this->getManifestFactory()->getPastellManifest()->getInfo());

        $this->setViewParameter('feature_toggle', $this->getObjectInstancier()
            ->getInstance(FeatureToggleService::class)
            ->getAllOptionalFeatures());
        $this->setViewParameter('display_feature_toggle_in_test_page', $this->getObjectInstancier()
            ->getInstance(FeatureToggleService::class)
            ->isEnabled(DisplayFeatureToggleInTestPage::class));
        $this->setViewParameter('template_milieu', "SystemEnvironnement");
        $this->setViewParameter('page_title', "Test du système");
        $this->setViewParameter('menu_gauche_select', self::SYSTEM_INDEX_PAGE);
        $this->renderDefault();
    }

    public function getPageNumber($page_name): int
    {
        $tab_number = [
        "system" => 0,
                                "flux" => 1,
                                "definition" => 2,
                                "connecteurs" => 4
        ];
        return $tab_number[$page_name];
    }

    /**
     * @throws NotFoundException
     */
    public function fluxAction()
    {
        $all_flux = [];
        $all_flux_restricted = [];

        $documentTypeValidation = $this->getDocumentTypeValidation();
        foreach ($this->getFluxDefinitionFiles()->getAll() as $id_flux => $flux) {
            $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id_flux);
            $all_flux[$id_flux]['nom'] = $documentType->getName();
            $all_flux[$id_flux]['type'] = $documentType->getType();
            $all_flux[$id_flux]['list_restriction_pack'] = $documentType->getListRestrictionPack();
            $definition_path = $this->getFluxDefinitionFiles()->getDefinitionPath($id_flux);
            $all_flux[$id_flux]['is_valide'] = $documentTypeValidation->validate($definition_path);
        }
        $this->setViewParameter('all_flux', $all_flux);

        foreach ($this->getFluxDefinitionFiles()->getAllRestricted() as $id_flux) {
            $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id_flux);
            $all_flux_restricted[$id_flux]['nom'] = $documentType->getName();
            $all_flux_restricted[$id_flux]['type'] = $documentType->getType();
            $all_flux_restricted[$id_flux]['list_restriction_pack'] = $documentType->getListRestrictionPack();
        }
        $this->setViewParameter('all_flux_restricted', $all_flux_restricted);

        $this->setViewParameter('template_milieu', "SystemFlux");
        $this->setViewParameter('page_title', "Types de dossier disponibles sur la plateforme");
        $this->setViewParameter('menu_gauche_select', "System/flux");
        $this->renderDefault();
    }


    private function getDocumentTypeValidation(): DocumentTypeValidation
    {
        /** @var ActionExecutorFactory $actionExecutorFactory */
        $actionExecutorFactory = $this->getViewParameterOrObject('ActionExecutorFactory');
        $all_action_class = $actionExecutorFactory->getAllActionClass();

        $list_pack = $this->getInstance(PackService::class)->getListPack();
        $all_connecteur_type = $this->getConnecteurDefinitionFiles()->getAllType();
        $all_type_entite = array_keys(Entite::getAllType());


        $connecteur_type_action_class_list = $this->getViewParameterOrObject('ConnecteurTypeFactory')->getAllActionExecutor();

        $documentTypeValidation = $this->getObjectInstancier()->getInstance(DocumentTypeValidation::class);
        $documentTypeValidation->setListPack($list_pack);
        $documentTypeValidation->setConnecteurTypeList($all_connecteur_type);
        $documentTypeValidation->setEntiteTypeList($all_type_entite);
        $documentTypeValidation->setActionClassList($all_action_class);
        $documentTypeValidation->setConnecteurTypeActionClassList($connecteur_type_action_class_list);
        return $documentTypeValidation;
    }

    private function getAllActionInfo(DocumentType $documentType, $type = 'flux'): array
    {
        $id = $documentType->getModuleId();
        $all_action = [];
        $action = $documentType->getAction();
        $action_list = $action->getAll();
        sort($action_list);
        foreach ($action_list as $action_name) {
            $class_name = $action->getActionClass($action_name);
            $element = [
                'id' => $action_name,
                'name' => $action->getActionName($action_name),
                'do_name' => $action->getDoActionName($action_name),
                'class' => $class_name,

                'action_auto' => $action->getActionAutomatique($action_name)
            ];

            if ($type == 'connecteur') {
                $element['path'] = $this->getActionExecutorFactory()->getConnecteurActionPath($id, $class_name);
            } else {
                $element['path'] = $this->getActionExecutorFactory()->getFluxActionPath($id, $class_name);
            }

            $all_action[] = $element;
        }
        return $all_action;
    }

    private function getFormsElement(DocumentType $documentType): array
    {
        $formulaire = $documentType->getFormulaire();

        $allFields = $formulaire->getAllFields();
        $form_fields = [];
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
        $this->setViewParameter('description', $documentType->getDescription());
        $this->setViewParameter('list_restriction_pack', $documentType->getListRestrictionPack());
        $this->setViewParameter('all_connecteur', $documentType->getConnecteur());

        $this->setViewParameter('all_action', $this->getAllActionInfo($documentType));

        $this->setViewParameter('formulaire_fields', $this->getFormsElement($documentType));

        $document_type_is_validate = false;
        $validation_error = false;
        try {
            $document_type_is_validate = $this->isDocumentTypeValid($id);
        } catch (Exception $e) {
            $validation_error = $this->getDocumentTypeValidation()->getLastError();
        }

        $this->setViewParameter('document_type_is_validate', $document_type_is_validate);
        $this->setViewParameter('validation_error', $validation_error);

        $this->setViewParameter('page_title', "Détail du type de dossier « $name » ($id)");
        $this->setViewParameter('template_milieu', "SystemFluxDetail");
        $this->setViewParameter('menu_gauche_select', "System/flux");

        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     */
    public function definitionAction()
    {
        $this->setViewParameter('flux_definition', $this->getDocumentTypeValidation()->getModuleDefinition());
        $this->setViewParameter('page_title', "Définition des types de dossier");
        $this->setViewParameter('template_milieu', "SystemFluxDef");
        $this->setViewParameter('menu_gauche_select', "System/definition");
        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     */
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
        $this->setViewParameter('all_connecteur_globaux', $all_connecteur_globaux);

        foreach ($this->getConnecteurDefinitionFiles()->getAll() as $id_connecteur => $connecteur) {
            $documentType = $this->getDocumentTypeFactory()->getEntiteDocumentType($id_connecteur);
            $all_connecteur_entite[$id_connecteur]['nom'] = $documentType->getName();
            $all_connecteur_entite[$id_connecteur]['description'] = $documentType->getDescription();
            $all_connecteur_entite[$id_connecteur]['list_restriction_pack'] = $documentType->getListRestrictionPack();
        }
        $this->setViewParameter('all_connecteur_entite', $all_connecteur_entite);

        foreach ($this->getConnecteurDefinitionFiles()->getAllRestricted(true) as $id_connecteur) {
            $documentType = $this->getDocumentTypeFactory()->getGlobalDocumentType($id_connecteur);
            $all_connecteur_globaux_restricted[$id_connecteur]['nom'] = $documentType->getName();
            $all_connecteur_globaux_restricted[$id_connecteur]['list_restriction_pack'] = $documentType->getListRestrictionPack();
        }
        $this->setViewParameter('all_connecteur_globaux_restricted', $all_connecteur_globaux_restricted);

        foreach ($this->getConnecteurDefinitionFiles()->getAllRestricted() as $id_connecteur) {
            $documentType = $this->getDocumentTypeFactory()->getEntiteDocumentType($id_connecteur);
            $all_connecteur_entite_restricted[$id_connecteur]['nom'] = $documentType->getName();
            $all_connecteur_entite_restricted[$id_connecteur]['list_restriction_pack'] = $documentType->getListRestrictionPack();
        }
        $this->setViewParameter('all_connecteur_entite_restricted', $all_connecteur_entite_restricted);

        $this->setViewParameter('page_title', "Connecteurs disponibles");
        $this->setViewParameter('template_milieu', "SystemConnecteurList");
        $this->setViewParameter('menu_gauche_select', "System/connecteur");
        $this->renderDefault();
    }


    /**
     * @param $id_flux
     * @return bool
     * @throws Exception
     */
    public function isDocumentTypeValid($id_flux): bool
    {
        $definition_path = $this->getFluxDefinitionFiles()->getDefinitionPath($id_flux);
        return $this->isDocumentTypeValidByDefinitionPath($definition_path);
    }

    /**
     * @param $definition_path
     * @return bool
     * @throws Exception
     */
    public function isDocumentTypeValidByDefinitionPath($definition_path): bool
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
     */
    public function mailTestAction(): void
    {
        $this->verifDroit(0, DroitService::getDroitLecture(DroitService::DROIT_SYSTEM));

        $email = $this->getPostInfo()->get('email');
        if (! $email) {
            $this->setLastError('Merci de spécifier un email');
            $this->redirect(self::SYSTEM_INDEX_PAGE);
        }

        $templatedEmail = (new TemplatedEmail())
            ->to(new Address($email))
            ->subject('[Pastell] Mail de test')
            ->htmlTemplate('test_system.html.twig')
            ->context(['SITE_BASE' => SITE_BASE])
            ->attachFromPath(
                __DIR__ . '/../connecteur/iParapheur/data-exemple/test-pastell-i-parapheur.pdf'
            );
        try {
            $pastellMailer = $this->getObjectInstancier()->getInstance(Mailer::class);
            $pastellMailer->send($templatedEmail);
            $this->setLastMessage(sprintf("Un email a été envoyé à l'adresse  : %s", get_hecho($email)));
        } catch (TransportExceptionInterface $e) {
            $this->setLastError(sprintf("Impossible d'envoyer le mail : %s", $e->getMessage()));
        }
        $this->redirect(self::SYSTEM_INDEX_PAGE);
    }

    public function phpinfoAction(): void
    {
        $this->needDroitEdition();
        phpinfo();
    }

    /**
     * @throws Exception
     */
    public function connecteurDetailAction()
    {
        $id_connecteur = $this->getGetInfo()->get('id_connecteur');
        $scope = $this->getGetInfo()->get('scope');
        if ($scope == 'global') {
            $documentType = $this->getDocumentTypeFactory()->getGlobalDocumentType($id_connecteur);
        } else {
            $documentType = $this->getDocumentTypeFactory()->getEntiteDocumentType($id_connecteur);
        }
        $name = $documentType->getName();
        $this->setViewParameter('description', $documentType->getDescription());
        $this->setViewParameter('list_restriction_pack', $documentType->getListRestrictionPack());
        $this->setViewParameter('all_action', $this->getAllActionInfo($documentType, 'connecteur'));
        $this->setViewParameter('formulaire_fields', $this->getFormsElement($documentType));

        $this->setViewParameter('page_title', "Détail du connecteur " . ($scope == 'global' ? 'global' : "d'entité") . " « $name » ($id_connecteur)");
        $this->setViewParameter('menu_gauche_select', "System/connecteur");
        $this->setViewParameter('template_milieu', "SystemConnecteurDetail");
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function sendWarningAction()
    {
        $this->getLogger()->warning("Warning emis par System/Warning");
        $this->setLastMessage("Un warning a été généré");
        $this->redirect(self::SYSTEM_INDEX_PAGE);
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
        $this->setViewParameter('login_page_configuration', file_exists(LOGIN_PAGE_CONFIGURATION_LOCATION)
            ? file_get_contents(LOGIN_PAGE_CONFIGURATION_LOCATION)
            : '');
        $this->setViewParameter('page_title', '');
        $this->setViewParameter('menu_gauche_select', 'System/loginPageConfiguration');
        $this->setViewParameter('template_milieu', 'LoginPageConfiguration');
        $this->renderDefault();
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doLoginPageConfigurationAction()
    {
        $this->needDroitEdition();

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
        $this->setViewParameter('page_title', 'Connecteurs manquants');
        $this->setViewParameter('template_milieu', 'SystemMissingConnecteur');
        $this->setViewParameter('menu_gauche_select', self::SYSTEM_INDEX_PAGE);

        $detail_manquant_list = [];
        $connecteur_manquant_list = $this->getConnecteurFactory()->getManquant();
        foreach ($connecteur_manquant_list as $id_connecteur) {
            $id_ce_list = $this->getConnecteurEntiteSQL()->getAllById($id_connecteur);
            foreach ($id_ce_list as $connecteur_info) {
                $detail_manquant_list[$id_connecteur][] = $connecteur_info;
            }
        }
        $this->setViewParameter('connecteur_manquant_list', $detail_manquant_list);

        $this->renderDefault();
    }

    /**
     * @throws Exception
     */
    public function exportAllMissingConnecteurAction()
    {
        $this->needDroitEdition();

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

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function emptyCacheAction(): void
    {
        $this->needDroitEdition();

        $redisWrapper = $this->getObjectInstancier()->getInstance(RedisWrapper::class);
        $redisWrapper->flushAll();
        $this->setLastMessage("Le cache Redis a été vidé");
        $this->redirect(self::SYSTEM_INDEX_PAGE);
    }
}
