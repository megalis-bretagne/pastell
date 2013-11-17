<?php 

class ManifestReader {
	
	private $ymlLoader;
	private $manifest_file_path;
	
	public function __construct(YMLLoader $ymlLoader, $manifest_file_path){
		$this->ymlLoader = $ymlLoader;
		$this->manifest_file_path = $manifest_file_path;
		
	}
	
	public function getInfo(){
		$result = $this->ymlLoader->getArray($this->manifest_file_path);
		
		foreach(array('version','revision','nom','description','pastell-version') as $key){
			if (! isset($result[$key])){
				$result[$key] = false;
			}
		}
		
		if (preg_match('#^\$Rev: (\d*) \$#',$result['revision'],$matches)){
			$result['revision'] = $matches[1];
		}
		$result['version-complete'] =  "Version {$result['version']} - R�vision  {$result['revision']}" ;
		return $result;
	}

	public function getRevision(){
		$info = $this->getInfo();
		return $info['revision'];
	}
	
	public function getVersion(){
		$info = $this->getInfo();
		return $info['version'];
	}
	
	public function isRevisionOK($version_attendue){
		$info = $this->getInfo();
		foreach($info['extensions_versions_accepted'] as $version_accepted ){
			if ($version_accepted == $version_attendue){
				return true;
			}
		}
		return false;
	}
}