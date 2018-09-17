<?php
class SystemControler extends PastellControler {

	public function _beforeAction(){
		parent::_beforeAction();
		$this->{'menu_gauche_template'} = "ConfigurationMenuGauche";
		$this->verifDroit(0,"system:lecture");
	}

	public function indexAction(){
		$this->verifDroit(0,"system:lecture");

		$this->{'droitEdition'}= $this->hasDroit(0, "system:edition");

		/** @var VerifEnvironnement $verifEnvironnement */
		$verifEnvironnement = $this->getInstance("VerifEnvironnement");

		$this->{'checkExtension'}=$verifEnvironnement->checkExtension();
		$this->{'checkPHP'}= $verifEnvironnement->checkPHP();
		$this->{'checkWorkspace'}= $verifEnvironnement->checkWorkspace();
		$this->{'checkModule'}= $verifEnvironnement->checkModule();
		$this->{'checkClasses'} = $verifEnvironnement->checkClasses();
		$this->{'valeurMinimum'}= array(
			"PHP" => $this->{'checkPHP'}['min_value'],
			"OpenSSL" => '1.0.0a',
		);
		$this->{'manifest_info'}= $this->getManifestFactory()->getPastellManifest()->getInfo();
		$cmd =  OPENSSL_PATH . " version";
		$openssl_version = `$cmd`;

		if (function_exists('curl_version')){
			$curl_ssl_version = curl_version()['ssl_version'];
		} else {
			$curl_ssl_version = "La fonction curl_version() n'existe pas !";
		}

		$database_client_encoding = $this->getSQLQuery()->getClientEncoding();


		$this->{'check_value'} = array(
			'PHP est en version 7.0' => array(
				'#^7\.0#',
				$this->{'checkPHP'}['environnement_value']
			),
			'OpenSSL est en version 1 ou plus ' => array(
				"#^OpenSSL 1\.#",
				$openssl_version
			),
			'Curl est compilé avec OpenSSL' => array(
				'#OpenSSL#',
				$curl_ssl_version
			),
			'La base de données est accédée en UTF-8' => array(
				"#^utf8$#",
				$database_client_encoding
			)
		);

		$data_expected =
		[
			'memory_limit' => "512M",
			'post_max_size' => "200M",
			'upload_max_filesize' => "200M"
		];

		$check_ini = [];
		foreach($data_expected as $key => $expteded_value){
			$check_ini[$key] = [
				'expected'=>$expteded_value,
				'actual'=>ini_get($key),
				'is_ok' => $expteded_value >= ini_get($key)
			];
		}
		$this->{'check_ini'}= $check_ini;





		$this->{'commandeTest'}= $verifEnvironnement->checkCommande(array('dot','xmlstarlet'));
		$this->{'redis_status'} = $verifEnvironnement->checkRedis();
		if (! $this->{'redis_status'}){
            $this->{'redis_last_error'} = $verifEnvironnement->getLastError();
        }

		$this->{'connecteur_manquant'}= $this->getConnecteurFactory()->getManquant();
		$this->{'document_type_manquant'}= $this->getTypeDocumentManquant();


		$freeSpace = $this->getObjectInstancier()->getInstance(FreeSpace::class);
		$this->{'free_space_data'} = $freeSpace->getFreeSpace(WORKSPACE_PATH);


		$this->{'template_milieu'}= "SystemEnvironnement";

		$this->{'page_title'}= "Test de l'environnement";
		$this->{'menu_gauche_select'} = "System/index";
		$this->renderDefault();
	}

	public function getPageNumber($page_name){
		$tab_number = array("system"=> 0,
								"flux" => 1,
								"definition" => 2,
								"connecteurs" => 4);
		return $tab_number[$page_name];
	}

	private function getTypeDocumentManquant(){
		$result = array();
		$document_type_list = $this->getDocument()->getAllType();
		$module_list = $this->getExtensions()->getAllModule();
		foreach($document_type_list as $document_type){
			if (empty($module_list[$document_type])){
				$result[] = $document_type;
			}
		}
		return $result;
	}
	
	public function fluxAction(){
		$all_flux = array();
		$documentTypeValidation = $this->getDocumentTypeValidation();
		foreach($this->getFluxDefinitionFiles()->getAll() as $id_flux => $flux){
			$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id_flux);
			$all_flux[$id_flux]['nom'] = $documentType->getName();
			$all_flux[$id_flux]['type'] = $documentType->getType();
			$definition_path =$this->getFluxDefinitionFiles()->getDefinitionPath($id_flux);
			$all_flux[$id_flux]['is_valide'] = $documentTypeValidation->validate($definition_path);
		}
		$this->{'all_flux'}= $all_flux;
		$this->{'template_milieu'}= "SystemFlux";
		$this->{'page_title'} = "Flux disponibles";
		$this->{'menu_gauche_select'} = "System/flux";
		$this->renderDefault();

	}


	private function getDocumentTypeValidation(){
		/** @var ActionExecutorFactory $actionExecutorFactory */
		$actionExecutorFactory = $this->{'ActionExecutorFactory'};
		$all_action_class = $actionExecutorFactory->getAllActionClass();

		$all_connecteur_type = $this->getConnecteurDefinitionFiles()->getAllType();
		$all_type_entite = array_keys(Entite::getAllType());


		$connecteur_type_action_class_list = $this->{'ConnecteurTypeFactory'}->getAllActionExecutor();

		/** @var DocumentTypeValidation $documentTypeValidation */
		$documentTypeValidation = $this->{'DocumentTypeValidation'};
		$documentTypeValidation->setConnecteurTypeList($all_connecteur_type);
		$documentTypeValidation->setEntiteTypeList($all_type_entite);
		$documentTypeValidation->setActionClassList($all_action_class);
		$documentTypeValidation->setConnecteurTypeActionClassList($connecteur_type_action_class_list);
		return $documentTypeValidation;
	}

	private function getAllActionInfo(DocumentType $documentType,$type='flux'){
	    $id = $documentType->getModuleId();
        $all_action = array();
        $action = $documentType->getAction();
        $action_list = $action->getAll();
        sort($action_list);
        foreach($action_list as $action_name){
            $class_name = $action->getActionClass($action_name);
            $element = array(
                'id'=> $action_name,
                'name' => $action->getActionName($action_name),
                'do_name' => $action->getDoActionName($action_name),
                'class' => $class_name,

                'action_auto' => $action->getActionAutomatique($action_name)
            );

            if ($type =='connecteur') {
                $element['path'] = $this->getActionExecutorFactory()->getConnecteurActionPath($id, $class_name);
            } else {
                $element['path'] = $this->getActionExecutorFactory()->getFluxActionPath($id,$class_name);
            }

            $all_action[] = $element;
        }
        return $all_action;
    }

    private function getFormsElement(DocumentType $documentType){
        $formulaire = $documentType->getFormulaire();

        $allFields = $formulaire->getAllFields();
        $form_fields = array();
        /** @var Field $field */
        foreach($allFields as $field){
            $form_fields[$field->getName()] = $field->getAllProperties();
        }
        return $form_fields;
    }

	public function fluxDetailAction(){
		$id = $this->getGetInfo()->get('id');
		$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id);
		$name = $documentType->getName();
		$this->{'description'}= $documentType->getDescription();
		$this->{'all_connecteur'}= $documentType->getConnecteur();

		$this->{'all_action'}= $this->getAllActionInfo($documentType);


		$this->{'formulaire_fields'}= $this->getFormsElement($documentType);

		$document_type_is_validate = false;
		$validation_error = false;
		try {
			$document_type_is_validate = $this->isDocumentTypeValid($id);
		} catch (Exception $e) {
			$validation_error = $this->getDocumentTypeValidation()->getLastError();
		}

		$this->{'document_type_is_validate'}= $document_type_is_validate;
		$this->{'validation_error'}= $validation_error;

		$this->{'page_title'}= "Détail du flux « $name »";
		$this->{'template_milieu'}= "SystemFluxDetail";
		$this->{'menu_gauche_select'} = "System/flux";

		$this->renderDefault();
	}

	public function definitionAction(){
		$this->{'flux_definition'}= $this->getDocumentTypeValidation()->getModuleDefinition();
		$this->{'page_title'}= "Définition des flux";
		$this->{'template_milieu'}= "SystemFluxDef";
		$this->{'menu_gauche_select'} = "System/definition";
		$this->renderDefault();
	}
	
	public function connecteurAction(){
		$this->{'all_connecteur_entite'}= $this->getConnecteurDefinitionFiles()->getAll();
		$this->{'all_connecteur_globaux'}= $this->getConnecteurDefinitionFiles()->getAllGlobal();
		$this->{'page_title'}= "Connecteurs disponibles";
		$this->{'template_milieu'}= "SystemConnecteurList";
		$this->{'menu_gauche_select'} = "System/connecteur";
		$this->renderDefault();
	}


	/**
	 * @param $id_flux
	 * @return bool
	 * @throws Exception
	 */
	public function isDocumentTypeValid($id_flux){
		$documentTypeValidation = $this->getDocumentTypeValidation();
		$definition_path =$this->getFluxDefinitionFiles()->getDefinitionPath($id_flux);

		if (! $documentTypeValidation->validate($definition_path)){
			throw new Exception(implode("\n",$this->getDocumentTypeValidation()->getLastError())) ;
		}
		return true;
	}

	public function mailTestAction(){
		$this->verifDroit(0,"system:edition");

		$email = $this->getPostInfo()->get("email");

		$this->getZenMail()->setEmetteur("Pastell",PLATEFORME_MAIL);
		$this->getInstance("ZenMail")->setReturnPath(PLATEFORME_MAIL);

		$this->getZenMail()->setDestinataire($email);
		$this->getZenMail()->setSujet("[Pastell] Mail de test");
		
		$this->getZenMail()->resetAttachment();
		$this->getZenMail()->addAttachment("exemple.pdf", __DIR__."/../documentation/data-exemple/exemple.pdf");
		
		$this->getZenMail()->setContenu(PASTELL_PATH . "/mail/test.php",array());
		$this->getZenMail()->send();
		
		$this->setLastMessage("Un email a été envoyé à l'adresse  : ".get_hecho($email));
		$this->redirect('System/index');
	}

	public function phpinfoAction(){
        $this->verifDroit(0,"system:edition");
        phpinfo();
        return;
    }

	/**
	 * @throws Exception
	 */
    public function connecteurDetailAction(){
        $this->verifDroit(0,"system:lecture");

        $id_connecteur = $this->getGetInfo()->get('id_connecteur');
        $scope = $this->getGetInfo()->get('scope');
        if ($scope == 'global'){
            $documentType = $this->getDocumentTypeFactory()->getGlobalDocumentType($id_connecteur);
        } else {
            $documentType = $this->getDocumentTypeFactory()->getEntiteDocumentType($id_connecteur);
        }
        $name = $documentType->getName();
        $this->{'description'}= $documentType->getDescription();
        $this->{'all_action'}= $this->getAllActionInfo($documentType,'connecteur');
        $this->{'formulaire_fields'}= $this->getFormsElement($documentType);

        $this->{'page_title'}= "Détail du connecteur ".($scope=='global'?'global':"d'entité")." « $name » ($id_connecteur)";
        $this->{'menu_gauche_select'} = "System/connecteur";
        $this->{'template_milieu'}= "SystemConnecteurDetail";
        $this->renderDefault();
    }

    public function sendWarningAction(){
    	$this->getLogger()->warning("Warning emis par System/Warning");
        $this->setLastMessage("Un warning a été généré");
        $this->redirect('System/index');
    }

    public function sendFatalErrorAction(){
        trigger_error("Déclenchement manuel d'une erreur fatale !", E_USER_ERROR);
    }

    public function changelogAction(){
        $this->verifDroit(0,"system:lecture");
        $this->{'page_title'}= "Journal des modifications (CHANGELOG)";
        $this->{'template_milieu'}= "SystemChangelog";
        $this->{'menu_gauche_select'} = "System/connecteur";

        $text = file_get_contents(__DIR__."/../CHANGELOG.md");
        $parsedown = new Parsedown();
        $text = $parsedown->parse($text);

        $text = preg_replace("/<h2>/","<h3>",$text);
        $this->{'changelog'} = preg_replace("/<h1>/","<h2>",$text);

	    $this->renderDefault();
    }

}
