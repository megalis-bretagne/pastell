<?php

class GEDTestCreateFolder extends ActionExecutor {

	public function go(){
		$cmis = $this->getMyConnecteur();
		$rootFolder = $cmis->getRootFolder();
				
		$folderName = $this->objectInstancier->PasswordGenerator->getPassword();
		$reponseCMIS = $cmis->createFolder($rootFolder,$folderName,"Pastell - Cr�ation d'un r�pertoire de test");
		if (! $reponseCMIS){
			$this->setLastMessage("La cr�ation du r�pertoire $folderName a �chou� : " . $cmis->getLastError());
			return false;
		}
		$this->setLastMessage("Cr�ation du r�pertoire $folderName : $reponseCMIS ");
		return true;
	}

}