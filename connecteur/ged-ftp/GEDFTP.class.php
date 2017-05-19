<?php 

class GEDFTP extends GEDConnecteur {
	
	private $server;
	private $login;
	private $password;
	private $passive_mode;
	private $folder;
	
	function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
		$this->server = $donneesFormulaire->get('server');
		$this->login = $donneesFormulaire->get('login');
		$this->password = $donneesFormulaire->get('password');
		$this->folder = $donneesFormulaire->get('folder');
		$this->passive_mode = $donneesFormulaire->get('passive_mode');
	}
	
	public function sendDonneesForumulaire(DonneesFormulaire $donneesFormulaire){
		$meta_data = $donneesFormulaire->getMetaData();
		$meta_data = preg_replace('#\\\"#', "", $meta_data);
		
		$file_name  = pathinfo(trim($donneesFormulaire->getFilePath("",""),"_"),PATHINFO_FILENAME);		
		$this->_createFolder($this->folder,$file_name);
		$doc_folder = $this->folder."/".$file_name;
		
		$file_tmp = tempnam("/tmp","metadata");
		file_put_contents($file_tmp,$meta_data);
		$this->_addDocument( $file_tmp,$doc_folder."/metadata.txt");
		unlink($file_tmp);
		
		$all_file = $donneesFormulaire->getAllFile();
		foreach($all_file as $field){
			$files = $donneesFormulaire->get($field);
			foreach($files as $num_file => $file_name){
				$file_path = $donneesFormulaire->getFilePath($field,$num_file);
				$this->_addDocument( $file_path,$doc_folder."/".basename($file_path));
			}
		}
		
		$file_tmp = tempnam("/tmp","vide");
		$this->_addDocument( $file_tmp,$doc_folder."/transfert_termine.txt");
		unlink($file_tmp);
	}
	
	private function _createFolder($folder,$new_folder_name){
		$folder_list = $this->listFolder($folder);
		if (in_array($new_folder_name, $folder_list)) {
			return;
		}
		
		$conn_id = $this->getConnection();
		
		@ ftp_chdir($conn_id,$folder) or $this->returnError();
		@ ftp_mkdir($conn_id,$new_folder_name) or  $this->returnError();
	}
	
	
	private function _addDocument($local_file,$remote_file){
		$conn_id = $this->getConnection();
		@ ftp_put($conn_id,$remote_file,$local_file,FTP_BINARY) or  $this->returnError();
	}
	
	public function getSanitizeFolderName($folder){
		$folder = strtr($folder," àáâãäçèéêëìíîïñòóôõöùúûüýÿ","_aaaaaceeeeiiiinooooouuuuyy");
		$folder = preg_replace('/[^\w_]/',"",$folder);
		return $folder;		
	}
	
	private function getConnection(){
		static $conn_id;
		if ($conn_id){
			return $conn_id;
		}
		
		@ $conn_id = ftp_connect($this->server) or $this->returnError(); 
		@ ftp_login($conn_id, $this->login, $this->password) or  $this->returnError();
		ftp_pasv($conn_id,$this->passive_mode?true:false); 
		return $conn_id;
	}
	
	public function createFolder($folder,$title,$description){}
	
	public function addDocument($title,$description,$contentType,$content,$gedFolder){}
	
	public function getRootFolder(){
		return $this->folder;
	}
	
	public function listFolder($folder){
		$conn_id = $this->getConnection();	
		
		$nlist = ftp_nlist($conn_id,$folder);
		if (!$nlist){
			return array();
		}
		
		//Attention, en fonction du serveur, les fichiers contiennent ou non le nom du répertoire !
		foreach($nlist as $file){
			$result[] = basename($file);
		}
		
		return $result;
	}
	
	public function returnError(){
		$last_error = error_get_last();
		throw new Exception($last_error['message']);
	}
	
}