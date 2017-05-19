<?php
class RecuperationFichierLocalTest extends ActionExecutor {
	
	public function go(){
		$directory_send_listing = "";
		$recuperationFichierLocal = $this->getMyConnecteur();
		$directory_listing = $recuperationFichierLocal->listFile();
		if ($recuperationFichierLocal->getDirectorySend()) {
			$directory_send_listing = $recuperationFichierLocal->listFile_send();
		}
		$this->setLastMessage("Connexion SSH OK. <br/>Contenu du répertoire : ".implode(", ",$directory_listing).". <br/>Contenu du répertoire de déplacement: ".implode(", ",$directory_send_listing));
		return true;
	}
	
}