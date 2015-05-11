<?php
class ManifestFactory {
	
	const MANIFEST_FILENAME = "manifest.yml";
	
	private $pastell_path;
	
	public function __construct($pastell_path){
		$this->pastell_path = $pastell_path;
	}
	
	public function getManifest($extension_path){
		$manifest_file_path  = $extension_path."/".self::MANIFEST_FILENAME;
		if (! $manifest_file_path){
			trigger_error("Le fichier $manifest_file_path n'existe pas",E_USER_WARNING);
			return false;
		}
		$ymlLoader = new YMLLoader();
		$manifest_info = $ymlLoader->getArray($manifest_file_path);
		if (!$manifest_info){
			trigger_error("Le fichier $manifest_file_path est vide",E_USER_WARNING);
			return false;
		}
		return new ManifestReader($manifest_info);
	}
	
	public function getPastellManifest(){
		return $this->getManifest($this->pastell_path);
	}
	
}