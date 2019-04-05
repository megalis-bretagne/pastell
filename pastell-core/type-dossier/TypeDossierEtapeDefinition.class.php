<?php

class TypeDossierEtapeDefinition {

	const TYPE_DOSSIER_ETAPE_DEFINITION_FILENAME = "type-dossier-etape.yml";


	private $ymlLoader;

	public function __construct(YMLLoader $ymlLoader) {
		$this->ymlLoader = $ymlLoader;
	}

	public function getFormulaireConfigurationEtape($type){
		return $this->getPart($type,'configuration_etape_formulaire');
	}

	public function getMapping(TypeDossierEtape $typeDossierEtape) : StringMapper
	{
		$stringMapper = new StringMapper();

		if (!$typeDossierEtape->etape_with_same_type_exists) {
			return $stringMapper;
		}

		$map_function_id = function ($original_value) use ($typeDossierEtape){
				return sprintf("%s_%d",$original_value,$typeDossierEtape->num_etape_same_type+1);
		};

		$map_onglet_name = function ($original_value) use ($typeDossierEtape){
			return sprintf("%s #%d",$original_value,$typeDossierEtape->num_etape_same_type+1);
		};


		foreach ($this->getPart($typeDossierEtape->type, DocumentType::FORMULAIRE) as $onglet_name => $element_list) {
			$stringMapper->add($onglet_name,$map_onglet_name($onglet_name));
			foreach($element_list as $element_id => $element_properties){
				$stringMapper->add($element_id,$map_function_id($element_id));
			}
		}
		foreach($this->getPart($typeDossierEtape->type,DocumentType::ACTION) as $action_id => $action_properties){
			$stringMapper->add($action_id,$map_function_id($action_id));
		}
		$stringMapper->add("envoi_{$typeDossierEtape->type}",$map_function_id("envoi_{$typeDossierEtape->type}"));
		return $stringMapper;
	}

	public function getPageCondition(TypeDossierEtape $typeDossierEtape){

		$page_condition = $this->getPart($typeDossierEtape->type,'page-condition');
		if (! $page_condition){
			return [];
		}
		$etape_with_same_type_exists = $typeDossierEtape->etape_with_same_type_exists;
		if (! $etape_with_same_type_exists){
			return $page_condition;
		}

		$stringMapper = $this->getMapping($typeDossierEtape);


		foreach($page_condition as $onglet_name => $onglet_condition){
			foreach($onglet_condition as $element_id => $element_condition){
				$new_element_id = $stringMapper->get($element_id);
				$page_condition[$onglet_name][$new_element_id] = $element_condition;
				unset($page_condition[$onglet_name][$element_id]);
			}
			$new_onglet_name = $stringMapper->get($onglet_name);
			$page_condition[$new_onglet_name] = $page_condition[$onglet_name];
			unset($page_condition[$onglet_name]);
		}

		return $page_condition;
	}

	public function getFormulaireForEtape(TypeDossierEtape $typeDossierEtape){
		$type = $typeDossierEtape->type;
		$etape_with_same_type_exists = $typeDossierEtape->etape_with_same_type_exists;

		$result =  $this->getPart($type,DocumentType::FORMULAIRE);

		if (! $etape_with_same_type_exists){
			return $result;
		}

		$stringMapper = $this->getMapping($typeDossierEtape);

		foreach($result as $onglet_id => $element_list){
			foreach($element_list as $element_id => $element_properties){

				if (isset($element_properties['choice-action'])){
					$stringMapper->map($result[$onglet_id][$element_id]['choice-action']);
				}
				$new_element_id = $stringMapper->get($element_id);
				$result[$onglet_id][$new_element_id] = $result[$onglet_id][$element_id];
				unset($result[$onglet_id][$element_id]);
			}
			$new_onglet_name = $stringMapper->get($onglet_id);
			$result[$new_onglet_name] = $result[$onglet_id];
			unset($result[$onglet_id]);
		}
		return $result;
	}



	public function getActionForEtape(TypeDossierEtape $typeDossierEtape){

		$type = $typeDossierEtape->type;
		$etape_with_same_type_exists = $typeDossierEtape->etape_with_same_type_exists;

		$result =  $this->getPart($type,'action');

		if (! $etape_with_same_type_exists){
			return $result;
		}

		$stringMapper = $this->getMapping($typeDossierEtape);

		foreach($result as $action_id => $action_properties){
			$new_action_id = $stringMapper->get($action_id);
			$result[$new_action_id] = $result[$action_id];
			unset($result[$action_id]);
		}

		foreach($result as $action_id => $action_properties){
			if (isset($action_properties[Action::ACTION_AUTOMATIQUE])){
				$stringMapper->map($result[$action_id][Action::ACTION_AUTOMATIQUE]);
			}
		}

		foreach($result as $action_id => $action_properties){
			if (isset($action_properties[Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION])) {
				foreach ($action_properties[Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION] as $num_last_action => $last_action) {
					$stringMapper->map($result[$action_id][Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION][$num_last_action]);
				}
			}
			if (isset($action_properties[Action::CONNECTEUR_TYPE_MAPPING])) {
				foreach ($action_properties[Action::CONNECTEUR_TYPE_MAPPING] as $key => $value) {
					$stringMapper->map($result[$action_id][Action::CONNECTEUR_TYPE_MAPPING][$key]);
				}
			}
		}

        $this->setActionName($typeDossierEtape,$result);
		return $result;
	}

	private function setActionName(TypeDossierEtape $typeDossierEtape,array & $result) : void{

        $map_action_name = function (& $original_value) use ($typeDossierEtape){
            $original_value = sprintf("%s #%d",$original_value,$typeDossierEtape->num_etape_same_type+1);
        };

	    foreach($result as $action_id => $action_properties){
            if (isset($action_properties[Action::ACTION_DISPLAY_NAME])) {
                $map_action_name($result[$action_id][Action::ACTION_DISPLAY_NAME]);
            }
            if (isset($action_properties[Action::ACTION_DO_DISPLAY_NAME])) {
                $map_action_name($result[$action_id][Action::ACTION_DO_DISPLAY_NAME]);
            }
        }
    }


	public function getConnecteurType($type){
        return $this->getPart($type,'connecteur_type')?:[$type];
    }

	private function getPart($type,$part){
		$etape_info = $this->getEtapeInfo($type);
		if (isset($etape_info[$part])){
			return $etape_info[$part];
		}
		return [];
	}

	private function getEtapeInfo($type){
		return $this->ymlLoader->getArray(__DIR__."/../../type-dossier/$type/".self::TYPE_DOSSIER_ETAPE_DEFINITION_FILENAME);
	}

	public function getLibelle($type){
		return $this->getPart($type,'libelle');
	}


	public function setSpecificData(TypeDossierEtape $etape,$result){

		$type = $etape->type;

		$type_dossier_etape_class = glob(__DIR__."/../../type-dossier/$type/TypeDossier*Etape.class.php");

		if (empty($type_dossier_etape_class)){
			return $result;
		}
		require_once $type_dossier_etape_class[0];

		$basename = basename($type_dossier_etape_class[0]);
		preg_match("#^(.*)\.class\.php$#",$basename,$matches);
		/**
		 * @var $typeDossierSpecificEtape TypeDossierEtapeSetSpecificInformation
		 */
		$typeDossierSpecificEtape = new $matches[1];


		return $typeDossierSpecificEtape->setSpecificInformation($etape,$result,$this->getMapping($etape));
	}

	public function getAllType(){
		$result = [];
		$type_dossier_etape_directory_list = glob(__DIR__."/../../type-dossier/*/");
		foreach($type_dossier_etape_directory_list as $dir){
			$type_dossier_etape = basename($dir);
			$result[$type_dossier_etape] = $this->getLibelle($type_dossier_etape);
		}
		return $result;
	}


}