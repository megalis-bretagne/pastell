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

	public function getPageCondition($type){
		return $this->getPart($type,'page-condition')?:[];
	}

	public function getFormulaire($type){
		return $this->getPart($type,'formulaire');
	}

	public function getActionForEtape(TypeDossierEtape $typeDossierEtape){
		return $this->getAction($typeDossierEtape->type,$typeDossierEtape->num_etape_same_type,$typeDossierEtape->etape_with_same_type_exists);
	}

	public function getAction($type,$num_etape_same_type = 0, $etape_with_same_type_exists = false){
		$result =  $this->getPart($type,'action');

		if (! $etape_with_same_type_exists){
			return $result;
		}

		$num_etape_same_type = $num_etape_same_type + 1;

		foreach($result as $action_id => $action_properties){
			$result["{$action_id}_{$num_etape_same_type}"] = $result[$action_id];
			$id_mapping[$action_id] = "{$action_id}_{$num_etape_same_type}";
			unset($result[$action_id]);
		}

		foreach($result as $action_id => $action_properties){
			if (isset($action_properties[Action::ACTION_AUTOMATIQUE]) && ! empty($id_mapping[$action_properties[Action::ACTION_AUTOMATIQUE]])){
				$result[$action_id][Action::ACTION_AUTOMATIQUE] = $id_mapping[$action_properties[Action::ACTION_AUTOMATIQUE]];
			}
		}

		foreach($result as $action_id => $action_properties){
			if (empty($action_properties[Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION])) {
				continue;
			}
			foreach($action_properties[Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION] as $num_last_action => $last_action){
				if (isset($id_mapping[$last_action])){
					$result[$action_id][Action::ACTION_RULE][Action::ACTION_RULE_LAST_ACTION][$num_last_action] = $id_mapping[$last_action];
				}
			}
		}
		return $result;
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


		return $typeDossierSpecificEtape->setSpecificInformation($etape,$result);
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