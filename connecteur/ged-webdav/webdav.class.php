<?php 

require_once( __DIR__ ."/lib/WebdavWrapper.class.php");

class webdav extends GEDConnecteur {
	
	private $url;
	private $user;
	private $password;
	private $folder;
	private $dav;
	
	function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
		$this->url = $donneesFormulaire->get('url').'/';
		$this->user = $donneesFormulaire->get('user');
		$this->password = $donneesFormulaire->get('password');
		$this->folder = $this->url;
		$this->dav = new WebdavWrapper($this->url, $this->user, $this->password);
	}
	
	public function sendDonneesForumulaire(DonneesFormulaire $donneesFormulaire){
		$meta_data = $donneesFormulaire->getMetaData();
		$meta_data = preg_replace('#\\\"#', "", $meta_data);
		
		$file_name  = pathinfo(trim($donneesFormulaire->getFilePath("",""),"_"),PATHINFO_FILENAME);		
		$this->_createFolder($this->folder,$file_name);
		$doc_folder = $this->folder."/".$file_name;
		
		$file_tmp = tempnam("/tmp","metadata");
		file_put_contents($file_tmp,$meta_data);
		$this->_addDocument($doc_folder,"metadata.txt",$meta_data);
		unlink($file_tmp);
		
		$all_file = $donneesFormulaire->getAllFile();
		foreach($all_file as $field){
			$files = $donneesFormulaire->get($field);
			foreach($files as $num_file => $file_name){
				$file_path = $donneesFormulaire->getFilePath($field,$num_file);
				$file_content = $donneesFormulaire->getFilecontent($field,$num_file);
				$this->_addDocument($doc_folder, basename($file_path), $file_content);
			}
		}	
		$file_tmp = tempnam("/tmp","vide");
		$this->_addDocument($doc_folder,"transfert_termine.txt",'');
		unlink($file_tmp);
	}
	
	public function listFolder($folder){
		try{
			$result = $this->dav->listFolder($folder);
		} catch (Exception $e){
			throw new Exception($e->getMessage());
		}
		return $result;
	}
	
	private function _createFolder($folder,$new_folder_name){
		try{
			$this->dav->createFolder($folder,$new_folder_name);
		} catch (Exception $e){
			return "Erreur : ".$e->getMessage();
		}
	}
		
	private function _addDocument($folder,$remote_file,$file_content){
		try{
			$this->dav->addDocument($folder,$remote_file,$file_content);
		} catch (Exception $e){
			return "Erreur : ".$e->getMessage();
		}
	}
	
	public function getSanitizeFolderName($folder){
		$folder = strtr($folder," àáâãäçèéêëìíîïñòóôõöùúûüýÿ","_aaaaaceeeeiiiinooooouuuuyy");
		$folder = preg_replace('/[^\w_]/',"",$folder);
		return $folder;		
	}
	
	private function getConnection(){}
	
	public function createFolder($folder,$title,$description){}
	
	public function addDocument($title,$description,$contentType,$content,$gedFolder){}
	
	public function getRootFolder(){
		return $this->folder;
	}
	
	public function returnError(){
		$last_error = error_get_last();
		throw new Exception($last_error['message']);
	}
	
}