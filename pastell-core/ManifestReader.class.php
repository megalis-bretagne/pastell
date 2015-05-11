<?php 

class ManifestReader {
	
	const VERSION = 'version';
	const REVISION = 'revision';
	const NOM = 'nom';
	const DESCRIPTION = 'description';
	const PASTELL_VERSION = 'pastell-version';
	const EXTENSIONS_VERSION_ACCEPTED = 'extensions_versions_accepted';
	const EXTENSION_NEEDED = 'extension_needed';
	
	private $ymlLoader;
	private $manifest_file_path;
	
	public function __construct(YMLLoader $ymlLoader, $manifest_file_path){
		$this->ymlLoader = $ymlLoader;
		$this->manifest_file_path = $manifest_file_path;
	}
	
	public function getInfo(){
		$result = $this->ymlLoader->getArray($this->manifest_file_path);
		
		foreach(array(self::VERSION,self::REVISION,self::NOM,self::DESCRIPTION,self::PASTELL_VERSION) as $key){
			if (! isset($result[$key])){
				$result[$key] = false;
			}
		}
		foreach(array(self::EXTENSIONS_VERSION_ACCEPTED,self::EXTENSION_NEEDED) as $key) {
			if (empty($result[$key])){
				$result[$key] = array();
			}	
		}
		
		if (preg_match('#^\$Rev: (\d*) \$#',$result[self::REVISION],$matches)){
			$result[self::REVISION] = $matches[1];
		}
		$result['version-complete'] =  "Version {$result[self::VERSION]} - Révision  {$result[self::REVISION]}" ;
		
		$result['autre-version-compatible'] = array();
		foreach($result[self::EXTENSIONS_VERSION_ACCEPTED] as $version){
			if ($version != $result[self::VERSION]){
				$result['autre-version-compatible'][] = $version;
			}	
		}
		return $result;
	}
	
	private function getElement($element_name){
		$info = $this->getInfo();
		if (empty($info[$element_name])){
			return false;
		}
		return $info[$element_name];
	}

	public function getRevision(){
		return $this->getElement(self::REVISION);
	}
	
	public function getVersion(){
		return $this->getElement(self::VERSION);
	}
	
	/**
	 * Teste si une version attendue correspond à une des versions accepté par le fichier manifest
	 * @param string $version_attendue
	 * @return boolean
	 */
	public function isVersionOK($version_attendue){
		$info = $this->getInfo();
		if (empty($info[self::EXTENSIONS_VERSION_ACCEPTED])){
			return false;
		}
		foreach($info[self::EXTENSIONS_VERSION_ACCEPTED] as $version_accepted ){
			if ($version_accepted == $version_attendue){
				return true;
			}
		}
		return false;
	}
	
	public function getExtensionNeeded(){
		return $this->getElement(self::EXTENSION_NEEDED);
	}
	
}