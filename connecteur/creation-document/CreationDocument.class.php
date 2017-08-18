<?php 

class CreationDocument extends Connecteur {
	
	const MANIFEST_FILENAME = 'manifest.xml';
	
	private $objectInstancier;	
	
	/**
	 * @var RecuperationFichier
	 */
	private $connecteurRecuperation;
	private $mode_auto;
    private $zip_exemple_name;
    private $zip_exemple_path;

    public function __construct(ObjectInstancier $objectInstancier){
        $this->objectInstancier = $objectInstancier;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
        $id_ce = $donneesFormulaire->get("connecteur_recup_id");
        $this->connecteurRecuperation = $this->objectInstancier->ConnecteurFactory->getConnecteurById($id_ce);
        $this->mode_auto = $donneesFormulaire->get('connecteur_auto');
        $this->zip_exemple_name = $donneesFormulaire->getFileName('zip_exemple');
        $this->zip_exemple_path = $donneesFormulaire->getFilePath('zip_exemple');

    }

    public function recupFileExemple($id_e){

        if (! $this->zip_exemple_name){
            return "Il n'y a pas de fichier zip";
        }
        if (substr($this->zip_exemple_name, -4) !== ".zip"){
            return "$this->zip_exemple_name n'est pas un fichier zip exemple";
        }
        $tmpFolder = $this->objectInstancier->TmpFolder->create();
        if (! copy($this->zip_exemple_path, $tmpFolder.'/'.$this->zip_exemple_name)) {
            return $this->zip_exemple_name." n'a pas été récupéré";
        }
        try{
            $result = $this->recupFileThrow($this->zip_exemple_name, $tmpFolder,$id_e);
        } catch (Exception $e){
            $this->objectInstancier->TmpFolder->delete($tmpFolder);
            return "Erreur lors de l'importation : ".$e->getMessage();
        }
        $this->objectInstancier->TmpFolder->delete($tmpFolder);

        return $result;

    }
	
	public function recupAllAuto($id_e){
		if (!$this->mode_auto){
			return array("Le mode automatique est désactivé");
		}
		return $this->recupAll($id_e);
	}
	
	public function recupAll($id_e){
		$result = array();
		foreach($this->connecteurRecuperation->listFile() as $file){
			if (in_array($file, array('.','..'))){
				continue;
			}
			$result[] = $this->recupFile($file,$id_e);
		}
		return $result;
	}
	
	private function recupFile($filename,$id_e){
		if (substr($filename, -4) !== ".zip"){
			return "$filename n'est pas un fichier zip";
		}
		$tmpFolder = $this->objectInstancier->TmpFolder->create();
		$this->connecteurRecuperation->retrieveFile($filename, $tmpFolder);
		try{
			$result = $this->recupFileThrow($filename, $tmpFolder,$id_e);
		} catch (Exception $e){
			$this->objectInstancier->TmpFolder->delete($tmpFolder);
			return "Erreur lors de l'importation : ".$e->getMessage();
		}
		$this->connecteurRecuperation->sendFile($tmpFolder,$filename);
		$this->connecteurRecuperation->deleteFile($filename);
		$this->objectInstancier->TmpFolder->delete($tmpFolder);

		return $result;
	}
	
	private function recupManifest($tmpFolder){
		foreach(scandir($tmpFolder) as $file){
			if (substr($file, -12) == self::MANIFEST_FILENAME) {
				return $file;
			}
		}
		return false;
	}

	private function recupFileThrow($filename,$tmpFolder,$id_e){
		$erreur = "";
		$isUTF8File = false;
		$isEnvoiAuto = false;
		
		$zip = new ZipArchive();
		$handle = $zip->open($tmpFolder."/".$filename);
		if (!$handle){
			throw new Exception("Impossible d'ouvrir le fichier zip");
		}
		$zip->extractTo($tmpFolder);
		$zip->close();
		$manifest_file = $tmpFolder."/".$this->recupManifest($tmpFolder);
		if (! file_exists($manifest_file)){
			throw new Exception("Le fihcier ".self::MANIFEST_FILENAME." n'a pas été trouvé dans l'archive");
		}
		$xml = simplexml_load_file($manifest_file);
		if (! $xml){
			throw new Exception("Le fichier ".self::MANIFEST_FILENAME." n'est pas lisible");
		}
		$pastell_type = strval($xml->attributes()->type);
		if (!$pastell_type){
			throw new Exception("L'attribut 'type' n'a pas été trouvé dans le manifest");
		}
		
		if (!$this->objectInstancier->DocumentTypeFactory->isTypePresent($pastell_type)){
			throw new Exception("Le type $pastell_type n'existe pas sur cette plateforme Pastell");
		}
		
		$new_id_d = $this->objectInstancier->Document->getNewId();
		$this->objectInstancier->Document->save($new_id_d,$pastell_type);
		$this->objectInstancier->DocumentEntite->addRole($new_id_d, $id_e, "editeur");
		
		$actionCreator = new ActionCreator($this->objectInstancier->SQLQuery,$this->objectInstancier->Journal,$new_id_d);
		
		$donneesFormulaire = $this->objectInstancier->DonneesFormulaireFactory->get($new_id_d);

		$isUTF8File=$this->isUTF8($manifest_file);
		
		foreach($xml->param as $param){
			$name = strval($param['name']);
			$value = strval($param['value']);
			if (($name == "envoi_auto") && ($value == "on")){
				$isEnvoiAuto=true;
			}
		}
		
		foreach($xml->data as $data){
			$name = strval($data['name']);
			if ($isUTF8File) {
				$value = $this->utf8_decode_array(strval($data['value']));
			}
			else {
				$value = strval($data['value']);
			}
			if ($donneesFormulaire->fieldExists($name)){
				$donneesFormulaire->setData($name,$value);
			}
		}
		
		$titre_fieldname = $donneesFormulaire->getFormulaire()->getTitreField();
		$titre = $donneesFormulaire->get($titre_fieldname);
		$this->objectInstancier->Document->setTitre($new_id_d,$titre);

		foreach($xml->files as $files){
			$name = strval($files['name']);
			if (! $donneesFormulaire->fieldExists($name)){
				continue;
			}
			$file_num = 0;
			foreach($files->file as $file){
				$content = strval($file['content']);
				$name_content = $this->utf8_decode_array(strval($file['content']));
				if (! file_exists($tmpFolder."/".$content)){
					$erreur .= "Le fichier $content n'a pas été trouvé.";
					continue;
				}
				$donneesFormulaire->addFileFromCopy($name,$name_content,$tmpFolder."/".$content,$file_num);
				$file_num++;
			}			
		}
		
		if (! $donneesFormulaire->isValidable()){
			$erreur .= $donneesFormulaire->getLastError();
		}
				
		if ($erreur) { // création avec erreur
			$actionCreator->addAction($id_e,0,Action::CREATION,"Importation du document (récupération) avec erreur: $erreur");
			return "Création du document avec erreur: #ID $new_id_d - type : $pastell_type - $titre - Erreur: $erreur";
		}
		else { // création succcès			
			$actionCreator->addAction($id_e,0,Action::MODIFICATION,"Importation du document (récupération) succès");
			if ($isEnvoiAuto) {
				$actionCreator->addAction($id_e,0,'importation',"Traitement du document");
				$this->objectInstancier->ActionExecutorFactory->executeOnDocument($id_e,0,$new_id_d,'orientation');
			}
			return "Création du document #ID $new_id_d - type : $pastell_type - $titre";
		}
	}
	
	private function utf8_decode_array($array){
		if (! is_array($array)){
			return utf8_decode($array);
		}
		$result = array();
		foreach ($array as $cle => $value) {
			$result[utf8_decode($cle)] = $this->utf8_decode_array($value);
		}
		return $result;
	}
	
	private function isUTF8($filename)
	{
		$info = finfo_open(FILEINFO_MIME_ENCODING);
		$type = finfo_buffer($info, file_get_contents($filename));
		finfo_close($info);
	
		return ($type == 'utf-8');
	}
}