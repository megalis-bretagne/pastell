<?php
//Chargement des fichier definition.yml dans les modules
class FluxDefinitionFiles {
	
	const DEFINITION_FILENAME = "definition.yml";
	
	private $extensions;
	private $yml_loader;
	
	public function __construct(Extensions $extensions, YMLLoader $yml_loader){
		$this->extensions = $extensions;
		$this->yml_loader = $yml_loader;
	}
	
	public function getAll(){
		$result = array();
		$all_module = $this->extensions->getAllModule();
		foreach ($all_module as $module_path){			
			$file_config = $module_path."/".self::DEFINITION_FILENAME;
			$config = $this->yml_loader->getArray($file_config);	
			$id_flux = basename(dirname($file_config));		
			$result[$id_flux] = $config;
		}
		uasort($result,array($this,"compareFluxDefinition"));
		return $result;
	}

	private function compareFluxDefinition($a,$b){
		return strcmp($a['nom'], $b['nom']);
	}
	
	public function getInfo($id_flux){
		return $this->yml_loader->getArray($this->getDefinitionPath($id_flux));
	}

	public function getDefinitionPath($id_flux){
		$module_path = $this->extensions->getModulePath($id_flux);
		return "$module_path/".self::DEFINITION_FILENAME;
	}

}