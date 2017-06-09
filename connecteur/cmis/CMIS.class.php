<?php

require_once(__DIR__."/lib/CMISWrapper.class.php");

class CMIS extends GEDConnecteur {

	private $url;
	private $login;
	private $password;
	private $folder;

	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		$this->url = $collectiviteProperties->get('ged_url');
		$this->login =$collectiviteProperties->get('ged_user_login');
		$this->password = $collectiviteProperties->get('ged_user_password');
		$this->folder = $collectiviteProperties->get('ged_folder');
	}
	
	public function getSanitizeFolderName($folder){
		return strtr($folder," /.", "___");
	}
	
	public function getRootFolder(){
		return $this->folder;
	}

	public function getRepositoryInfo(){
		$cmisWrapper = new CMISWrapper($this->login,$this->password);
		return $cmisWrapper->getRepositoryInfo($this->url);
	}
	
	public function testObject(){
		$cmisWrapper = new CMISWrapper($this->login,$this->password);
		return $cmisWrapper->getObjectByPath($this->url,$this->folder);
	}

	public function addDocument($title,$description,$contentType,$content,$gedFolder){
		$cmisWrapper = new CMISWrapper($this->login,$this->password);
		return $cmisWrapper->addDocument($this->url,$title,$description,$contentType,$content,$gedFolder);
	}
	
	public function createFolder($folder,$title,$description){
		$cmisWrapper = new CMISWrapper($this->login,$this->password);
		return $cmisWrapper->createFolder($this->url,$folder,$title,$description);
	}

    public function listFolder($folder){
    	throw new Exception("Not implemented");
    }
    
	
}