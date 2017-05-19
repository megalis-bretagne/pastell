<?php 

class GEDEnvoiDocumentASigner extends ActionExecutor {
	
	public function go(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$ged = $this->getConnecteur("GED");
		
		$folder = $ged->getRootFolder();
		
		$folder_name = $donneesFormulaire->get("libelle");
		
		$folder_name = $ged->getSanitizeFolderName($folder_name);

		$sub_folder = rtrim($folder,"/"). "/" . $folder_name;

		$ged->createFolder($folder,$folder_name,"Pastell - Flux Document");

		$meta_data = $donneesFormulaire->getMetaData();
		$ged->addDocument("metadata.txt","Meta données du document","text/plain",$meta_data,$sub_folder);
		
		$all_file = $donneesFormulaire->getAllFile();
		foreach($all_file as $field){
			$files = $donneesFormulaire->get($field);
			foreach($files as $num_file => $file_name){
				$description = $this->getFormulaire()->getField($field)->getLibelle();
				$content = $this->getDonneesFormulaire()->getFileContent($field,$num_file);
				$contentType =  $this->getDonneesFormulaire()->getContentType($field,$num_file);
				$ged->addDocument($file_name,$description,$contentType,$content,$sub_folder);
			}
		}	
		
		$ged->sendDonneesForumulaire($this->getDonneesFormulaire());
		
		$this->addActionOK("Document envoyé sur la GED");
		
		$actionName  = $this->getActionName();
		$this->setLastMessage("L'action $actionName a été executé sur le document");
		$this->notify($this->action, $this->type,"L'action $actionName a été executé sur le document");
		
		return true;
	}
	
}