<?php 

class GEDEnvoi extends ActionExecutor {
	
	public function go(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$ged = $this->getConnecteur("GED");
		
		$folder = $ged->getRootFolder();
		
		$folder_name = $donneesFormulaire->get("objet");
		
		$folder_name = $ged->getSanitizeFolderName($folder_name);
		
		$ged->createFolder($folder,$folder_name,"Pastell - Flux Actes");
		$sub_folder = $folder ."/$folder_name";
		
		$meta_data = $donneesFormulaire->getMetaData();
		$ged->addDocument("metadata.txt","Meta donn�es de l'actes","plain/text",$meta_data,$sub_folder);
		
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
		
		$this->setLastMessage("Document envoy� en GED");
		
		$actionName  = $this->getActionName();
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,$this->action,"Document envoy� sur la GED");
		$this->setLastMessage("L'action $actionName a �t� execut� sur le document");
		return true;
	}
	
}