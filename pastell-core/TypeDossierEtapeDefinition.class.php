<?php

class TypeDossierEtapeDefinition {

	private $ymlLoader;

	public function __construct(YMLLoader $ymlLoader) {
		$this->ymlLoader = $ymlLoader;
	}

	public function getFormulaireConfigurationEtape($type){
		$etape_info = $this->getEtapeInfo($type);
		if (isset($etape_info['configuration_etape_formulaire'])){
			return $etape_info['configuration_etape_formulaire'];
		}
		return [];
	}

	public function getAction($type){
		$etape_info = $this->getEtapeInfo($type);
		if (isset($etape_info['action'])){
			return $etape_info['action'];
		}
		return [];
	}

	private function getEtapeInfo($type){
		return $this->ymlLoader->getArray(__DIR__."/../common-yaml/type-dossier-etape-{$type}.yml");
	}


}