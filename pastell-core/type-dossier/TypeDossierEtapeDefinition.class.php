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

	public function getAction($type){
		return $this->getPart($type,'action');
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