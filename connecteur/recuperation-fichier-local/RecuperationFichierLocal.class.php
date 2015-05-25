<?php
class RecuperationFichierLocal extends RecuperationFichier {
	
	private $directory;
	private $directory_send;
	
	public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
		$this->directory = $donneesFormulaire->get("directory");
		$this->directory_send = $donneesFormulaire->get("directory_send");
	}
	
	public function listFile() {
		$scan = scandir($this->directory);
		if (! $scan) {
			throw new Exception($this->directory." n'a pas �t� scann�");
		}
		return $scan;
	}
	
	public function retrieveFile($filename, $destination_directory){
		if (! copy($this->directory."/$filename", $destination_directory."/".$filename)) {
			throw new Exception($filename." n'a pas �t� r�cup�r�");
		}
		return true;
	}
	
	public function deleteFile($filename){
		if (! unlink($this->directory."$filename")) {
			throw new Exception($filename." n'a pas �t� supprim�");
		}
		return true;
	}
	
	public function sendFile($source_directory, $filename){
		if ($this->directory_send) {
			if (! copy($source_directory."/".$filename, $this->directory_send."/$filename")) {
				throw new Exception($filename." n'a pas �t� d�pos�");
			}
		}
		return true;
	}
	
}