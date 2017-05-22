<?php
class RecuperationFichierLocalTest extends ActionExecutor {
	
	public function go(){
		/** @var RecuperationFichierLocal $recuperationFichierLocal */
		$recuperationFichierLocal = $this->getMyConnecteur();
		$directory_listing = $recuperationFichierLocal->listFile();
		$message = "Lecture du répertoire OK. <br/>Contenu du répertoire : ".implode(", ",$directory_listing);
		if ($recuperationFichierLocal->getDirectorySend()) {
			$directory_send_listing = $recuperationFichierLocal->listFile_send();
			$message .= " <br/>Contenu du répertoire de déplacement: ".implode(", ",$directory_send_listing);
		}

		$this->setLastMessage($message);
		return true;
	}
	
}