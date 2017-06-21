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
	    return preg_replace("#[^A-Za-z0-9éèçàêîâôûùüÉÈÇÀÊÎÂÔÛÙÜ]#u","_",$folder);
	}


	
	public function getRootFolder(){
		return $this->folder;
	}

	private function getCMISWrapper(){
	    return new CMISWrapper($this->login,$this->password);
    }

	public function getRepositoryInfo(){
		$info =  $this->getCMISWrapper()->getRepositoryInfo($this->url);
		return $info;
	}
	
	public function testObject(){
		return $this->getCMISWrapper()->getObjectByPath($this->url,$this->folder);
	}

	public function addDocument($title,$description,$contentType,$content,$gedFolder){
		return $this->getCMISWrapper()->addDocument($this->url,$title,$description,$contentType,$content,$gedFolder);
	}
	
	public function createFolder($folder,$title,$description){
		return $this->getCMISWrapper()->createFolder($this->url,$folder,$title,$description);
	}

    public function listFolder($folder){
    	throw new Exception("Not implemented");
    }
    
	
}