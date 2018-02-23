<?php
class GlaneurLocalListerRepertoires extends ActionExecutor {
	
	public function go(){
		/** @var GlaneurLocal $glaneurLocal */
        $glaneurLocal = $this->getMyConnecteur();
		$directory_listing = $glaneurLocal->listFile($glaneurLocal->getDirectory());
		$message = "Lecture du répertoire OK. <br/>Contenu du répertoire : ".implode(", ",$directory_listing);
		if ($glaneurLocal->getDirectorySend()) {
			$directory_send_listing = $glaneurLocal->listFile($glaneurLocal->getDirectorySend());
			$message .= " <br/>Contenu du répertoire de déplacement: ".implode(", ",$directory_send_listing);
		}

		$this->setLastMessage($message);

		return true;
	}
	
}