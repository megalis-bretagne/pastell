<?php

class GEDSSH extends GEDConnecteur {

    /** @var  DonneesFormulaire */
    private $donneesFormulaire;

    private $ssh2;

    private $folder_name;

    public function __construct(SSH2 $ssh2){
        $this->ssh2 = $ssh2;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
        $this->donneesFormulaire = $donneesFormulaire;
    }

    protected function getProperties($name){
        return $this->donneesFormulaire->get($name);
    }

    protected function getFilePath($name){
        return $this->donneesFormulaire->getFilePath($name);
    }

    protected function configSSH2(){
        $this->ssh2->setServerName(
            $this->getProperties('ssh_server'),
            $this->getProperties('ssh_fingerprint'),
            $this->getProperties('ssh_port')
        );
        $this->ssh2->setPubKeyAuthentication(
            $this->getFilePath('ssh_public_key'),
            $this->getFilePath('ssh_private_key'),
            $this->getProperties('ssh_private_password')
        );
        $this->ssh2->setPasswordAuthentication(
            $this->getProperties('ssh_login'),
            $this->getProperties('ssh_password')
        );
    }
    
    public function getRootFolder(){
        return $this->getProperties('ssh_directory');
    }

    public function listFolder($folder) {
        $this->configSSH2();
        $directory_listing = $this->ssh2->listDirectory($folder);
        if (!$directory_listing){
            throw new Exception($this->ssh2->getLastError());
        }
        return $directory_listing;
    }

    public function sendDonneesForumulaire(DonneesFormulaire $donneesFormulaire){

        if (($this->getProperties('ssh_mode_transfert') == 1) || ($this->getProperties('ssh_mode_transfert')== 2)){
            $this->_createFolder($this->folder_name);
            $folder = $this->getProperties("ssh_directory")."/{$this->folder_name}/";

            $this->metadataXml($donneesFormulaire, $folder);

            if ($this->getProperties('ssh_mode_transfert') == 1) {
                $this->FileNameOriginal($donneesFormulaire, $folder);
            }
            else {
                $this->FileNamePastell($donneesFormulaire, $folder);
            }
        }
        else {
            $file_name  = pathinfo(trim($donneesFormulaire->getFilePath("",""),"_"),PATHINFO_FILENAME);
            $this->_createFolder($file_name);
            $folder = $this->getProperties("ssh_directory")."/".$file_name;

            $this->metadataTxt($donneesFormulaire, $folder);
            $this->FileNamePastell($donneesFormulaire, $folder);
        }

        $file_tmp = tempnam("/tmp","transfert_termine.txt");
        $this->_addDocument($file_tmp,$folder."/transfert_termine.txt");
        unlink($file_tmp);
        return true;

    }

	public function testCreateDirAndFile(){
		$dir = "test_".time();
		$this->_createFolder($dir);
		$absolute_path = $this->getProperties("ssh_directory")."/$dir/test.txt";
		$this->_addDocument(__DIR__."/fixtures/test.txt",$absolute_path);
		return $absolute_path;
	}

    private function _createFolder($new_folder_name){
        $this->configSSH2();
        if (! $this->ssh2->createFolder($this->getProperties("ssh_directory")."/".$new_folder_name)){
			throw new Exception("Impossible de créer le répertoire $new_folder_name . ".$this->ssh2->getLastError());
        }
        return true;
    }


	public function forceCreateFolder($new_folder_name){
		$this->configSSH2();

		if (! $this->ssh2->createFolder($new_folder_name)){
			throw new Exception("Impossible de créer le répertoire $new_folder_name . ".$this->ssh2->getLastError());
		}
		return true;
	}

    protected function _addDocument($local_path, $path_on_server){
        $this->configSSH2();
        if (! $this->ssh2->sendFile($local_path, $path_on_server)){
			throw new Exception("Impossible de créer le document $path_on_server. " . $this->ssh2->getLastError());
        }
        return true;
    }

    public function createFolder($folder,$title,$description){
        $this->folder_name = $title;
    }

    public function addDocument($title,$description,$contentType,$content,$gedFolder){}

	public function forceAddDocument($local_path, $path_on_server){
		return $this->_addDocument($local_path,$path_on_server);
	}


	public function returnError(){
        $last_error = error_get_last();
        throw new Exception($last_error['message']);
    }

    public function metadataTxt(DonneesFormulaire $donneesFormulaire, $folder){

        $meta_data = $donneesFormulaire->getMetaData();
        $meta_data = preg_replace('#\\\"#', "", $meta_data);

        $file_tmp = tempnam("/tmp","metadata");
        file_put_contents($file_tmp,$meta_data);
        $this->_addDocument($file_tmp,$folder."/metadata.txt");
        unlink($file_tmp);

        return true;
    }

    public function metadataXml(DonneesFormulaire $donneesFormulaire, $folder){

        $metaDataXML = new MetaDataXML();
        $metadata_xml = $metaDataXML->getMetaDataAsXML($donneesFormulaire);
        $file_tmp = sys_get_temp_dir()."/".mt_rand(0,mt_getrandmax());
        file_put_contents($file_tmp,$metadata_xml);
        $this->_addDocument($file_tmp,$folder."/metadata.xml");
        unlink($file_tmp);

        return true;
    }

    public function FileNamePastell(DonneesFormulaire $donneesFormulaire, $folder){

        $all_file = $donneesFormulaire->getAllFile();
        foreach($all_file as $field) {
            $files = $donneesFormulaire->get($field);
            foreach ($files as $num_file => $file_name) {
                $file_path = $donneesFormulaire->getFilePath($field, $num_file);
                $this->_addDocument($file_path, $folder . "/" . basename($file_path));
            }
        }
        return true;
    }

    public function FileNameOriginal(DonneesFormulaire $donneesFormulaire, $folder){

        $all_file = $donneesFormulaire->getAllFile();
        $already_send = array();
        foreach($all_file as $field){
            $files = $donneesFormulaire->get($field);
            foreach($files as $num_file => $file_name){
                if (empty($already_send[$file_name])) {
                    $file_name_original = $donneesFormulaire->getFileName($field,$num_file);
                    $file_name_original = $this->getSanitizeFileName($file_name_original);
                    $this->_addDocument(
                        $donneesFormulaire->getFilePath($field,$num_file),
                        $folder."/".$file_name_original
                    );
                }
                $already_send[$file_name] = true;
            }
        }
        return true;
    }

}