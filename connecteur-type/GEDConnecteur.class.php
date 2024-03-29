<?php 

abstract class GEDConnecteur extends Connecteur {

    /** @var  DonneesFormulaire */
    protected $connecteurConfig;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
        $this->connecteurConfig = $donneesFormulaire;
    }

    public function send(DonneesFormulaire $donneesFormulaire){}


    /** @deprecated */
    public function sendDonneesForumulaire(DonneesFormulaire $donneesFormulaire){
        $this->send($donneesFormulaire);
    }

    /** @deprecated */
	public function createFolder($folder,$title,$description){}

    /** @deprecated */
    public function addDocument($title,$description,$contentType,$content,$gedFolder){}

    /** @deprecated */
    public function getRootFolder(){}

    /** @deprecated */
    public function listFolder($folder){}

    /** @deprecated */
	public function getSanitizeFolderName($folder){
		$folder = strtr($folder," àáâãäçèéêëìíîïñòóôõöùúûüýÿ","_aaaaaceeeeiiiinooooouuuuyy");
		$folder = preg_replace('/[^\w_]/',"",$folder);
		return $folder;
	}

    /** @deprecated */
	public function getSanitizeFileName($file){
		$file = strtr($file," àáâãäçèéêëìíîïñòóôõöùúûüýÿ","_aaaaaceeeeiiiinooooouuuuyy");
		$file = preg_replace('/[^\w-_\.]/',"",$file);
		return $file;
	}

    /** @deprecated */
	public function forceAddDocument($local_path, $path_on_server){}

	/** @deprecated */
	public function forceCreateFolder($new_folder_name){}
}


class GEDExceptionAlreadyExists extends Exception {}