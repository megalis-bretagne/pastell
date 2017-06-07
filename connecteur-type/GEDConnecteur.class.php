<?php 

abstract class GEDConnecteur extends Connecteur {
	abstract public function createFolder($folder,$title,$description);
	abstract public function addDocument($title,$description,$contentType,$content,$gedFolder);
	abstract public function getRootFolder();
	abstract public function listFolder($folder);
	public function sendDonneesForumulaire(DonneesFormulaire $donneesFormulaire){}

	public function getSanitizeFolderName($folder){
		$folder = strtr($folder," àáâãäçèéêëìíîïñòóôõöùúûüýÿ","_aaaaaceeeeiiiinooooouuuuyy");
		$folder = preg_replace('/[^\w_]/',"",$folder);
		return $folder;
	}

	public function getSanitizeFileName($file){
		$file = strtr($file," àáâãäçèéêëìíîïñòóôõöùúûüýÿ","_aaaaaceeeeiiiinooooouuuuyy");
		$file = preg_replace('/[^\w-_\.]/',"",$file);
		return $file;
	}
}