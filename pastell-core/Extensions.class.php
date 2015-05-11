<?php 

class Extensions {
	
	const MODULE_FOLDER_NAME = "module";
	const CONNECTEUR_FOLDER_NAME = "connecteur";
	const CONNECTEUR_TYPE_FOLDER_NAME = "connecteur-type";
	
	const MANIFEST_FILENAME = "manifest.yml";
	
	private $extensionSQL;
	private $pastellManifestReader;
	private $pastell_path;
	
	public function __construct(ExtensionSQL $extensionSQL, ManifestReader $pastellManifestReader,$pastell_path){
		$this->extensionSQL = $extensionSQL;
		$this->pastellManifestReader = $pastellManifestReader;
		$this->pastell_path = $pastell_path;
	}
	
	public function getAll(){
		$extensions_list = array();
		foreach($this->extensionSQL->getAll() as $extension){
			$extensions_list[$extension['id_e']] = $this->getInfo($extension['id_e']); 
		}
		return $extensions_list;
	}
	
	public function getAllConnecteur(){
		$result = array();
		foreach($this->getAllExtensionsPath() as $search){
			foreach($this->getAllConnecteurByPath($search) as $id_connecteur){
				$result[$id_connecteur] = $search."/".self::CONNECTEUR_FOLDER_NAME."/$id_connecteur";
			}
		}
		return $result;
	}
	
	public function getConnecteurPath($id_connecteur){
		$result = $this->getAllConnecteur();
		if (empty($result[$id_connecteur])){
			return false;
		}
		return $result[$id_connecteur];
	}
	
	
	private function getAllExtensionsPath(){
		$to_search = array($this->pastell_path);
		foreach($this->extensionSQL->getAll() as $extension){
			$to_search[] = $extension['path'];
		}
		return $to_search;
	}
	
	public function getAllModule(){
		$result = array();
		foreach($this->getAllExtensionsPath() as $search){
			foreach($this->getAllModuleByPath($search) as $id_module){
				$result[$id_module] = $search."/".self::MODULE_FOLDER_NAME."/$id_module";
			}
		}
		return $result;
	}
	
	public function getModulePath($id_module_to_found){
		$result = $this->getAllModule();
		if (empty($result[$id_module_to_found])){
			return false;
		}
		return $result[$id_module_to_found];
	}
	
	public function getInfo($id_e){
		$info = $this->extensionSQL->getInfo($id_e);
		$info = $this->getInfoFromPath($info['path']);
		$info['error'] = false;
		$info['warning'] = false;
		
		$info['id_e'] = $id_e;
		if (! file_exists($info['path'])){
			$info['error'] = "Extension non-trouvé";
			$info['error-detail'] = "L'emplacement {$info['path']} n'a pas été trouvé sur le système de fichier";
		} else if (! $info['manifest']['nom']){
			$info['warning'] = "manifest.yml absent";
			$info['warning-detail'] = "Le fichier manifest.yml n'a pas été trouvé dans {$info['path']}";	
		} else if (! $this->pastellManifestReader->isRevisionOK($info['manifest']['pastell-version'])) {
			$version = $this->pastellManifestReader->getVersion();
			$info['warning'] = "Version de pastell incorrecte";
			$info['warning-detail'] = "Ce module attend une version de Pastell ({$info['manifest']['pastell-version']}) non prise en charge par ce Pastell";
		}
		return $info;
	}
	
	private function getInfoFromPath($path){
		$result['path'] = $path; 
		$result['nom'] = basename($path);
		$result['flux'] = $this->getAllModuleByPath($path);
		$result['connecteur'] = $this->getAllConnecteurByPath($path);
		$result['connecteur-type'] = $this->getAllConnecteurTypeByPath($path);
		$result['manifest'] = $this->getManifest($path);
		return $result;
	}
	
	private function getManifest($path){
		$manifestReader = new ManifestReader(new YMLLoader(), "$path/".self::MANIFEST_FILENAME);
		return $manifestReader->getInfo();
	}
	
	private function getAllModuleByPath($path){
		return $this->globAll($path."/".self::MODULE_FOLDER_NAME."/*");
	}
	
	private function getAllConnecteurByPath($path){
		return $this->globAll($path."/".self::CONNECTEUR_FOLDER_NAME."/*");
	}
	
	private function getAllConnecteurTypeByPath($path){
		return $this->globAll($path."/".self::CONNECTEUR_TYPE_FOLDER_NAME."/*");
	}
	
	private function globAll($glob_expression){
		$result = array();
		foreach (glob($glob_expression) as $file_config){			
			$result[] =  basename($file_config);
		}
		return $result;
	}

	/**
	 * Permet de mettre dans le path l'ensemble des répertoires connecteurs-type des modules.
	 * Les connecteurs types des modules sont chargés après celui du coeur Pastell (c-à-d on ne peut pas masquer un connecteur-type du coeur Pastell)  
	 */
	public function loadConnecteurType(){
		$extensions_path_list = $this->getAllExtensionsPath();
		foreach($extensions_path_list as $extension_path){
			$connecteur_type_path = $extension_path."/".self::CONNECTEUR_TYPE_FOLDER_NAME."/"; 
			if (file_exists($connecteur_type_path)){
				set_include_path(get_include_path() . PATH_SEPARATOR . $connecteur_type_path);
			}
		}
	}
	
}