<?php

require_once __DIR__."/../../lib/MetaDataXML.class.php";

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
        if ($this->getProperties('ssh_mode_transfert') == 1){
            return $this->sendDonneesFormulaireOriginal($donneesFormulaire);
        }
        $meta_data = $donneesFormulaire->getMetaData();
        $meta_data = preg_replace('#\\\"#', "", $meta_data);

        $file_name  = pathinfo(trim($donneesFormulaire->getFilePath("",""),"_"),PATHINFO_FILENAME);
        $this->_createFolder($file_name);
        $doc_folder = $this->getProperties("ssh_directory")."/".$file_name;

        $file_tmp = tempnam("/tmp","metadata");
        file_put_contents($file_tmp,$meta_data);
        $this->_addDocument($file_tmp,$doc_folder."/metadata.txt");
        unlink($file_tmp);

        $all_file = $donneesFormulaire->getAllFile();
        foreach($all_file as $field){
            $files = $donneesFormulaire->get($field);
            foreach($files as $num_file => $file_name){
                $file_path = $donneesFormulaire->getFilePath($field,$num_file);
                $this->_addDocument($file_path, $doc_folder."/".basename($file_path));
            }
        }
        $file_tmp = tempnam("/tmp","transfert_termine.txt");
        $this->_addDocument($file_tmp,$doc_folder."/transfert_termine.txt");
        unlink($file_tmp);
        return true;
    }

    public function sendDonneesFormulaireOriginal(DonneesFormulaire $donneesFormulaire){
        $this->_createFolder($this->folder_name);
        $folder = $this->getProperties("ssh_directory")."/{$this->folder_name}/";

        $all_file = $donneesFormulaire->getAllFile();
        $already_send = array();
        foreach($all_file as $field){
            $files = $donneesFormulaire->get($field);
            foreach($files as $num_file => $file_name){
                if (empty($already_send[$file_name])) {
                    $this->_addDocument(
                        $donneesFormulaire->getFilePath($field,$num_file),
                        $folder."/".$donneesFormulaire->getFileName($field,$num_file)
                    );
                }
                $already_send[$file_name] = true;
            }
        }

        $metaDataXML = new MetaDataXML();
        $metadata_xml = $metaDataXML->getMetaDataAsXML($donneesFormulaire);
        $file_tmp = sys_get_temp_dir()."/".mt_rand(0,mt_getrandmax());
        file_put_contents($file_tmp,$metadata_xml);
        $this->_addDocument($file_tmp,$folder."/metadata.xml");

        $file_tmp = tempnam("/tmp","transfert_termine.txt");
        $this->_addDocument($file_tmp,$folder."/transfert_termine.txt");
        unlink($file_tmp);
        return true;
    }

    private function _createFolder($new_folder_name){
        $this->configSSH2();
        if (! $this->ssh2->createFolder($this->getProperties("ssh_directory")."/".$new_folder_name)){
            throw new Exception($this->ssh2->getLastError());
        }
        return true;
    }

    protected function _addDocument($local_path, $path_on_server){
        $this->configSSH2();
        if (! $this->ssh2->sendFile($local_path, $path_on_server)){
            throw new Exception($this->ssh2->getLastError());
        }
        return true;
    }

    public function getSanitizeFolderName($folder){
        $folder = strtr($folder," àáâãäçèéêëìíîïñòóôõöùúûüýÿ","_aaaaaceeeeiiiinooooouuuuyy");
        $folder = preg_replace('/[^\w_]/',"",$folder);
        return $folder;
    }

    public function createFolder($folder,$title,$description){
        $this->folder_name = $title;
    }

    public function addDocument($title,$description,$contentType,$content,$gedFolder){}

    public function returnError(){
        $last_error = error_get_last();
        throw new Exception($last_error['message']);
    }


}