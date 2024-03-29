<?php 

class GEDEnvoiDocumentASigner extends ActionExecutor {
	
	public function go(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$ged = $this->getConnecteur("GED");
		
		$folder = $ged->getRootFolder();

		$folder_name = $this->getFolderName($donneesFormulaire);

        try {
            $ged->createFolder($folder, $folder_name, "Pastell - Flux document");
        } catch (GEDExceptionAlreadyExists $e){
            $folder_name = $folder_name."_".date("YmdHis")."_".mt_rand(0,mt_getrandmax());
            $ged->createFolder($folder, $folder_name, "Pastell - Flux document");
        }
        $sub_folder = rtrim($folder,"/"). "/" . $folder_name;

		$meta_data = $donneesFormulaire->getMetaData();
		$ged->addDocument("metadata.txt","Meta données du document","text/plain",$meta_data,$sub_folder);
		
		$all_file = $donneesFormulaire->getAllFile();
		foreach($all_file as $field){
			$files = $donneesFormulaire->get($field);
			foreach($files as $num_file => $file_name){
				$description = $this->getFormulaire()->getField($field)->getLibelle();
				$content = $donneesFormulaire->getFileContent($field,$num_file);
				$contentType =  $donneesFormulaire->getContentType($field,$num_file);
				$ged->addDocument($file_name,$description,$contentType,$content,$sub_folder);
			}
		}	
		
		$ged->sendDonneesForumulaire($donneesFormulaire);
		
		$this->addActionOK("Document envoyé sur la GED");
		
		$actionName  = $this->getActionName();
		$this->setLastMessage("L'action $actionName a été executée sur le document");
		$this->notify($this->action, $this->type,"L'action $actionName a été executée sur le document");

		return true;
	}

    public function getFolderName(DonneesFormulaire $donneesFormulaire){

        try{
            /** @var ParametrageFluxDoc $parametrageFlux */
            $parametrageFlux = $this->getConnecteur("ParametrageFlux");
        } catch (Exception $e){
            return $donneesFormulaire->get("libelle");
        }
        if ($parametrageFlux) {
            $folder_name = $parametrageFlux->getGedDirectoryName($donneesFormulaire);
        }
        else {
            $folder_name = $donneesFormulaire->get("libelle");
        }

        return $folder_name;

    }
	
}