<?php 

class HeliosGEDEnvoi extends ActionExecutor {
	
	public function go(){
		/** @var GEDConnecteur $ged */
		$ged = $this->getConnecteur("GED");
		
		$folder = $ged->getRootFolder();
		
		$folder_name = $this->getDonneesFormulaire()->get("objet");
		$folder_name = $ged->getSanitizeFolderName($folder_name);

		$sub_folder = rtrim($folder,"/"). "/" . $folder_name;
		
		$ged->createFolder($folder,$folder_name,"Pastell - Flux Helios ");
		
		foreach(array(
					'fichier_pes',
					'visuel_pdf',
					'iparapheur_historique',
					'fichier_pes_signe',
					'document_signe',
					'fichier_reponse'
		) as $key){
			$this->sendFile($sub_folder,$key);
		}	
		
		$ged->sendDonneesForumulaire($this->getDonneesFormulaire());		
		
		$this->addActionOK("Document envoyé sur la GED");
		
		$actionName  = $this->getActionName();
		$this->notify($this->action, $this->type,"L'action $actionName a été executé sur le document");
		
		$this->setLastMessage("L'action $actionName a été executé sur le document");
		return true;
	}
	
	public function sendFile($folder,$key){
		/** @var GEDConnecteur $ged */
		$ged = $this->getConnecteur("GED");		
		
		if ($this->getFormulaire()->getField($key)){
			$description = $this->getFormulaire()->getField($key)->getLibelle();
		} else {
			$description = $key;
		}
				
		$content = $this->getDonneesFormulaire()->getFileContent($key);
		if (!$content){
			return false;
		}
		
		$filename = $this->getDonneesFormulaire()->getFileName($key);
		$contentType =  $this->getDonneesFormulaire()->getContentType($key);

		if ($key == 'fichier_pes_signe'){
			$filename = 'signe_'.$filename;
		}

		return $ged->addDocument($filename,$description,$contentType,$content,$folder);
	}
}