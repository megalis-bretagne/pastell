<?php

class SedaNGFluxInfo extends ChoiceActionExecutor {

	public function getMyConnecteurConfig(){
		return $this->getConnecteurConfig($this->id_ce);
	}

	public function go(){
		/** @var SedaNG $sedaNG */
		$sedaNG = $this->getMyConnecteur();
		$properties = $sedaNG->getProprietePastellFlux();

		$recuperateur = new Recuperateur($_POST);
		$data = array();
		foreach($properties as $property){
			$data[$property] = utf8_encode($recuperateur->get($property));
		}
		$this->getMyConnecteurConfig()->addFileFromData('flux_info_content',"properties.json",json_encode($data));
		$this->getMyConnecteurConfig()->setData('flux_info',count($properties). " propriété(s)");

	}

	public function display() {
		/** @var SedaNG $sedaNG */
		$sedaNG = $this->getMyConnecteur();
		$properties = array_fill_keys($sedaNG->getProprietePastellFlux(),'');

		$file_content = $this->getMyConnecteurConfig()->getFileContent('flux_info_content');
		if ($file_content){
			foreach(json_decode($file_content,true) as $property=>$value) {
				if (isset($properties[$property])){
					$properties[$property] = utf8_decode($value);
				}
			}
		}
		$this->properties = $properties;

		$this->renderPage("Propriétés « pastell:flux » du profil",__DIR__."/../template/SedaNGConnecteurProperties.php");
		return true;
	}

	public function displayAPI() {
		
	}

}