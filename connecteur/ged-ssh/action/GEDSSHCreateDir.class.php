<?php

class GEDSSHCreateDir extends ActionExecutor {

	public function go(){
		/** @var GEDSSH $sshConnecteur */
		$sshConnecteur = $this->getMyConnecteur();

		$directory = $sshConnecteur->testCreateDirAndFile();
		$this->setLastMessage("Création du fichier $directory : OK");
		return true;
	}

}