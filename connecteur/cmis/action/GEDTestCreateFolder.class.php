<?php

class GEDTestCreateFolder extends ActionExecutor {

	public function go(){
		/** @var  $cmis CMIS*/
		$cmis = $this->getMyConnecteur();
		$rootFolder = $cmis->getRootFolder();

		$folderName = "Répertoire de test. ".mt_rand(0,mt_getrandmax());
		$folderName = $cmis->getSanitizeFolderName($folderName);
		$sub_folder = rtrim($rootFolder,"/"). "/" . $folderName;

		
		$reponseCMIS = $cmis->createFolder($rootFolder,$folderName,"Pastell - Création d'un répertoire de test");
		if (! $reponseCMIS){
			$this->setLastMessage("La création du répertoire $folderName a échoué : " . $cmis->getLastError());
			return false;
		}

		$cmis->addDocument("alfresco-m-a-tué.txt","ceci est un test","text/plain","toto titicontent",$sub_folder);

		$this->setLastMessage("Création du répertoire $folderName avec un document dedans ");
		return true;
	}

}