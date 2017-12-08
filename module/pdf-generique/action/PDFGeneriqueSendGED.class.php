<?php

class PDFGeneriqueSendGED extends ActionExecutor  {

	private $zip_before_send;
	private $file_list;
	private $send_metadata;


	public function go(){
	    /*
		$this->decodeConfigFile();
		if ($this->zip_before_send){
		    $date = date("YmdHis");
			$zip_path = "/tmp/{$this->id_d}_{$date}.zip";
			$zip = new ZipArchive();
			if (!$zip->open($zip_path, ZIPARCHIVE::CREATE)) {
				throw new Exception("Impossible de créer une archive");
			}
			foreach($this->file_list as $filename => $filepath){
				$zip->addFile($filepath,$filename);
			}

			if ($this->send_metadata){
				$meta_data_filename = "/tmp/{$this->id_d}_metadata.yml";
				file_put_contents($meta_data_filename,$this->getDonneesFormulaire()->getMetaData());
				$zip->addFile($meta_data_filename,"metadata.yml");
			}
			$zip->close();

			$destination = $this->getGedConnecteur()->getRootFolder()."/".basename($zip_path);
			$this->getGedConnecteur()->forceAddDocument($zip_path,$destination);
            // DÃ©but tmp cmis (//à supp pour Pastell V2)
            $this->getGedConnecteur()->addDocument(basename($zip_path),"Archive du document",$this->getContentType($zip_path),file_get_contents($zip_path),$this->getGedConnecteur()->getRootFolder());
            // Fin tmp cmis
			unlink($zip_path);
			if ($this->send_metadata && isset($meta_data_filename)) {
				unlink($meta_data_filename);
			}
			$this->setLastMessage("Le fichier ZIP a été déposé vers $destination");
			$this->notify($this->action, $this->type,"Le fichier ZIP a été déposé vers $destination");
            $this->addActionOK("Le fichier ZIP a été déposé vers $destination");
			return true;
		}
        $date = date("YmdHis");

		$sub_folder = $this->getGedConnecteur()->getRootFolder()."/{$this->id_d}_{$date}";

		$this->getGedConnecteur()->forceCreateFolder($sub_folder);

        // DÃ©but tmp cmis (//à supp pour Pastell V2)
        $folder = $this->getGedConnecteur()->getRootFolder();
		$folder_name = "{$this->getDonneesFormulaire()->getTitre()}_{$date}";
		$folder_name = $this->getGedConnecteur()->getSanitizeFolderName($folder_name);
		$sub_folder_cmis = rtrim($folder,"/"). "/" . $folder_name;
		$this->getGedConnecteur()->createFolder($folder,$folder_name,"Pastell - Flux pdf");
        // Fin tmp cmis

		if ($this->send_metadata) {
			$meta_data_filename = "/tmp/{$this->id_d}_metadata.yml";
			file_put_contents($meta_data_filename,$this->getDonneesFormulaire()->getMetaData());
			$this->getGedConnecteur()->forceAddDocument($meta_data_filename,"$sub_folder/metadata.yml");
            // DÃ©but tmp cmis (//à supp pour Pastell V2)
            $this->getGedConnecteur()->addDocument("metadata.txt","Meta données du document","text/plain",file_get_contents($meta_data_filename),$sub_folder_cmis);
            // Fin tmp cmis
			unlink($meta_data_filename);
		}

		foreach($this->file_list as $filename => $filepath){
			$this->getGedConnecteur()->forceAddDocument($filepath,$sub_folder."/".$filename);
            // DÃ©but tmp cmis (//à supp pour Pastell V2)
            $this->getGedConnecteur()->addDocument($filename,$filename,$this->getContentType($filepath),file_get_contents($filepath),$sub_folder_cmis);
            // Fin tmp cmis
		}
*/
        $this->getGedConnecteur()->send($this->getDonneesFormulaire());
		$titre = $this->getDonneesFormulaire()->getTitre();
		$message = "Le document {$titre} a été versé sur le dépôt";

		$this->setLastMessage($message);
		$this->notify($this->action, $this->type,$message);
		$this->addActionOK($message);
		return true;
	}
/*
	private function decodeConfigFile(){
		$donneesFormulaire = $this->getDonneesFormulaire();

		if ($this->action == 'send-ged-1'){
		    $file_config = 'ged_config_1';
        } elseif ($this->action == 'send-ged-2'){
            $file_config = 'ged_config_2';
        } else {
            throw new Exception("Action non-attendue pour l'objet PDFGeneriqueSendGED");
        }

		$ged_config_content = $donneesFormulaire->getFileContent($file_config);

		$this->zip_before_send = false;
		$this->send_metadata = true;
		$file_list = array();

		if ($ged_config_content){
			$ged_config = json_decode($ged_config_content,true);
			if (! $ged_config){
				throw new Exception("Impossible de décoder le fichier de configuration de la GED");
			}

			$this->zip_before_send = isset($ged_config['zip'])?$ged_config['zip']:false;
			$this->send_metadata = isset($ged_config['metadata'])?$ged_config['metadata']:false;
			$file_list = isset($ged_config['file'])?$ged_config['file']:array();
		}
		if (! $file_list){
			$file_list = $donneesFormulaire->getAllFile();
		}

		$this->file_list = $this->getFilelist($file_list);
	}
*/

	private function getGedConnecteur(){
		/** @var GEDConnecteur $connecteur */
		$connecteur = $this->getConnecteur("GED");
		return $connecteur;
	}
/*
	private function getFilelist(array $field_list){
		$result = array();
		foreach($field_list as $field) {
			$files = $this->getDonneesFormulaire()->get($field);
			if (!$files) {
				continue;
			}
			foreach ($files as $num_file => $file_name) {
				$result[$file_name] = $this->getDonneesFormulaire()->getFilePath($field, $num_file);
			}
		}
		return $result;
	}
*/
/*
    // DÃ©but tmp cmis (//à supp pour Pastell V2)
    //http://stackoverflow.com/questions/6595183/docx-file-type-in-php-finfo-file-is-application-zip
    private function getOpenXMLMimeType($file_name){
        $ext = pathinfo($file_name,PATHINFO_EXTENSION);
        $openXMLExtension = array(
            'xlsx' => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            'xltx' => "application/vnd.openxmlformats-officedocument.spreadsheetml.template",
            'potx' =>  "application/vnd.openxmlformats-officedocument.presentationml.template",
            'ppsx' =>  "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
            'pptx'   =>  "application/vnd.openxmlformats-officedocument.presentationml.presentation",
            'sldx'   =>  "application/vnd.openxmlformats-officedocument.presentationml.slide",
            'docx'   =>  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            'dotx'   =>  "application/vnd.openxmlformats-officedocument.wordprocessingml.template",
            'xlam'   =>  "application/vnd.ms-excel.addin.macroEnabled.12",
            'xlsb'   =>  "application/vnd.ms-excel.sheet.binary.macroEnabled.12");
        if (isset($openXMLExtension[$ext])){
            return $openXMLExtension[$ext];
        }
        return false;
    }

    public function getContentType($file_path){
        if (! file_exists($file_path)){
            return false;
        }
        $fileInfo = new finfo();
        $result = $fileInfo->file($file_path,FILEINFO_MIME_TYPE);
        if ($result == 'application/zip'){
            $file_name = basename($file_path);
            $result = $this->getOpenXMLMimeType($file_name)?:'application/zip';
        }
        return $result;
    }
    // Fin tmp cmis
*/
}