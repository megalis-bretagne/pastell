<?php

class GEDSSHCreateDir extends ActionExecutor {

	public function go(){
		/** @var GEDSSH $sshConnecteur */
		$sshConnecteur = $this->getMyConnecteur();

		$directory = $sshConnecteur->testCreateDirAndFile();
		$this->setLastMessage("Cr�ation du fichier $directory : OK");
		return true;
	}

}