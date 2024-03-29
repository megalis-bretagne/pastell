<?php 

class ManifestReader {
	
	const ID = 'id';
	const VERSION = 'version';
	const REVISION = 'revision';
	const NOM = 'nom';
	const DESCRIPTION = 'description';
	const PASTELL_VERSION = 'pastell-version';
	const EXTENSIONS_VERSION_ACCEPTED = 'extensions_versions_accepted';
	const EXTENSION_NEEDED = 'extension_needed';
	
	const VERSION_COMPLETE = 'version-complete';
	const AUTRE_VERSION_COMPATIBLE = 'autre-version-compatible';
	const LAST_CHANGED_DATE = 'last_changed_date';
	
	private $manifest_info;
	
	public function __construct(array $manifest_info){
		foreach(array(self::VERSION,self::REVISION,self::NOM,self::DESCRIPTION,self::PASTELL_VERSION,self::ID,self::LAST_CHANGED_DATE) as $key){
			if (! isset($manifest_info[$key])){
				$manifest_info[$key] = false;
			}
		}
		foreach(array(self::EXTENSIONS_VERSION_ACCEPTED,self::EXTENSION_NEEDED) as $key) {
			if (empty($manifest_info[$key])){
				$manifest_info[$key] = array();
			}
		}
		if (preg_match('#^\$Rev: (\d*) \$#u',$manifest_info[self::REVISION],$matches)){
			$manifest_info[self::REVISION] = $matches[1];
		}
		$manifest_info[self::VERSION_COMPLETE] =  "Version {$manifest_info[self::VERSION]} - Révision  {$manifest_info[self::REVISION]}" ;
		
		$manifest_info[self::AUTRE_VERSION_COMPATIBLE] = array();
		foreach($manifest_info[self::EXTENSIONS_VERSION_ACCEPTED] as $version){
			if ($version != $manifest_info[self::VERSION]){
				$manifest_info[self::AUTRE_VERSION_COMPATIBLE][] = $version;
			}
		}
		
		$this->manifest_info = $manifest_info;
	}
	
	public function getInfo(){
		return $this->manifest_info;
	}
	
	private function getElement($element_name){
		$info = $this->getInfo();
		return $info[$element_name];
	}

	public function getRevision(){
		return $this->getElement(self::REVISION);
	}
	
	public function getVersion(){
		return $this->getElement(self::VERSION);
	}
	
	public function getId(){
		return $this->getElement(self::ID);
	}
	
	public function getLastChangedDate(){
		return $this->getElement(self::LAST_CHANGED_DATE);
	}
	
	/**
	 * Teste si une version attendue correspond à une des versions acceptées par le fichier manifest
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