<?php
class RecuperationFichierSSHTest extends ActionExecutor {
	
	public function go(){
		$directory_send_listing = "";
		$recuperationFichierSSH = $this->getMyConnecteur();
		$directory_listing = $recuperationFichierSSH->listFile();
		if ($recuperationFichierSSH->getProperties("ssh_directory_send")) {
			$directory_send_listing = $recuperationFichierSSH->listFile_send();
		}
		$this->setLastMessage("Connexion SSH OK. <br/>Contenu du répertoire : ".implode(", ",$directory_listing).". <br/>Contenu du répertoire de déplacement : ".implode(", ",$directory_send_listing));
		return true;
	}
	
}