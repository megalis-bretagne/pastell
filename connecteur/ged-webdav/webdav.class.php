<?php 

require_once( __DIR__ ."/lib/WebdavWrapper.class.php");

class webdav extends GEDConnecteur {
	
	private $url;
	private $user;
	private $password;
	private $folder;
	private $dav;
	private $mode_transfert;
	private $folder_name;

	function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
		$this->url = $donneesFormulaire->get('url').'/';
		$this->user = $donneesFormulaire->get('user');
		$this->password = $donneesFormulaire->get('password');
		$this->folder = $this->url;
		$this->dav = new WebdavWrapper($this->url, $this->user, $this->password);
	}

    public function sendDonneesForumulaire(DonneesFormulaire $donneesFormulaire){

        if ($this->mode_transfert == 1) { //"Nommage des fichiers avec nom original, métadonnée en XML"
            $this->_createFolder($this->folder,$this->folder_name);
            $folder = $this->folder."/$this->folder_name/";

            $this->metadataXml($donneesFormulaire, $folder);
            $this->FileNameOriginal($donneesFormulaire, $folder);
        }
        elseif ($this->mode_transfert == 2) { //"Nommage des fichiers avec nom Pastell, métadonnée en XML"
            $this->_createFolder($this->folder,$this->folder_name);
            $folder = $this->folder."/$this->folder_name/";

            $fileNamePastell = true;
            $this->metadataXml($donneesFormulaire, $folder, $fileNamePastell);
            $this->FileNamePastell($donneesFormulaire, $folder);
        }
        else { //"Les fichiers Pastell sont directement envoyé sans traitement"
            $file_name  = pathinfo(trim($donneesFormulaire->getFilePath("",""),"_"),PATHINFO_FILENAME);
            $this->_createFolder($this->folder,$file_name);
            $folder = $this->folder."/".$file_name;

            $this->metadataTxt($donneesFormulaire, $folder);
            $this->FileNamePastell($donneesFormulaire, $folder);
        }

        $file_tmp = tempnam("/tmp","transfert_termine.txt");
        $this->_addDocument($folder,"transfert_termine.txt"," ");
        unlink($file_tmp);
        return true;
    }

	public function testCreateDirAndFile(){

		$dir = "test_".time();
		$this->_createFolder($this->folder,$dir);
		$folder = $this->folder."/$dir/";
		$absolute_path = $this->folder."/$dir/test.txt";
		$file_content = file_get_contents(__DIR__."/fixtures/test.txt");
		$this->_addDocument($folder,"test.txt",$file_content);
		return $absolute_path;
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
            throw new Exception("Erreur : ".$e->getMessage());
        }

    }

    private function _addDocument($folder,$remote_file,$file_content){
        try{
            $this->dav->addDocument($folder,$remote_file,$file_content);
        } catch (Exception $e){
            throw new Exception("Erreur : ".$e->getMessage());
        }
    }
	
	private function getConnection(){}

	public function createFolder($folder,$title,$description){
		$this->folder_name = $title;
	}

	public function addDocument($title,$description,$contentType,$content,$gedFolder){}
	
	public function getRootFolder(){
		return $this->folder;
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
        $this->_addDocument($folder,"metadata.txt",$meta_data);
        unlink($file_tmp);

        return true;
    }

    public function metadataXml(DonneesFormulaire $donneesFormulaire, $folder, $fileNamePastell = false){

        $metaDataXML = new MetaDataXML();
        $metadata_xml = $metaDataXML->getMetaDataAsXML($donneesFormulaire, $fileNamePastell);

        $file_tmp = sys_get_temp_dir()."/".mt_rand(0,mt_getrandmax());
        file_put_contents($file_tmp,$metadata_xml);
        $this->_addDocument($folder,"metadata.xml",$metadata_xml);
        unlink($file_tmp);

        return true;
    }

    public function FileNamePastell(DonneesFormulaire $donneesFormulaire, $folder){

        $all_file = $donneesFormulaire->getAllFile();
        foreach($all_file as $field){
            $files = $donneesFormulaire->get($field);
            foreach($files as $num_file => $file_name){
                $file_path = $donneesFormulaire->getFilePath($field,$num_file);
                $file_content = $donneesFormulaire->getFilecontent($field,$num_file);
                $this->_addDocument($folder, basename($file_path), $file_content);
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
                    $file_content = $donneesFormulaire->getFilecontent($field,$num_file);
                    $this->_addDocument($folder,$file_name_original,$file_content);
                }
                $already_send[$file_name] = true;
            }
        }
        return true;
    }

}