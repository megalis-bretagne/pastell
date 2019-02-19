<?php

class TypeDossierEtapeDefinition {

	private $ymlLoader;

	public function __construct(YMLLoader $ymlLoader) {
		$this->ymlLoader = $ymlLoader;
	}

	public function getFormulaireConfigurationEtape($type){
		return $this->getPart($type,'configuration_etape_formulaire');
	}

	public function getPageCondition($type){
		return $this->getPart($type,'page-condition');
	}

	public function getFormulaire($type){
		return $this->getPart($type,'formulaire');
	}

	public function getAction($type){
		return $this->getPart($type,'action');
	}

	private function getPart($type,$part){
		$etape_info = $this->getEtapeInfo($type);
		if (isset($etape_info[$part])){
			return $etape_info[$part];
		}
		return [];
	}

	private function getEtapeInfo($type){
		return $this->ymlLoader->getArray(__DIR__."/../type-dossier/$type/type-dossier-etape-{$type}.yml");
	}


}