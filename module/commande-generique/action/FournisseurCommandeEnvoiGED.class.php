<?php

class FournisseurCommandeEnvoiGED extends ActionExecutor {

	public function go(){
		$donneesFormulaire = $this->getDonneesFormulaire();
		$ged = $this->getConnecteur("GED");

		$folder = $ged->getRootFolder();

		$folder_name = $donneesFormulaire->get("libelle");

		$folder_name = $ged->getSanitizeFolderName($folder_name);


        try {
            $ged->createFolder($folder, $folder_name, "Pastell - Flux commande générique");
        } catch (GEDExceptionAlreadyExists $e){
            $folder_name = $folder_name."_".date("YmdHis")."_".mt_rand(0,mt_getrandmax());
            $ged->createFolder($folder, $folder_name, "Pastell - Flux commande générique");
        }
        $sub_folder = rtrim($folder,"/"). "/" . $folder_name;


		$meta_data = $donneesFormulaire->getMetaData();
		$ged->addDocument("metadata.txt","Meta données de la commande","text/plain",$meta_data,$sub_folder);

		$all_file = $donneesFormulaire->getAllFile();
		$already_send = array();
		foreach($all_file as $field){
			$files = $donneesFormulaire->get($field);
			foreach($files as $num_file => $file_name){
				$description = $this->getFormulaire()->getField($field)->getLibelle();
				$content = $this->getDonneesFormulaire()->getFileContent($field,$num_file);
				$contentType =  $this->getDonneesFormulaire()->getContentType($field,$num_file);
				if (empty($already_send[$file_name])) {
					$ged->addDocument($file_name, $description, $contentType, $content, $sub_folder);
				}
				$already_send[$file_name] = true;
			}
		}

		$ged->sendDonneesForumulaire($this->getDonneesFormulaire());

		$this->addActionOK("Document envoyé sur la GED");

		$actionName  = $this->getActionName();
		$this->setLastMessage("L'action $actionName a été executée sur le document");
		$this->notify($this->action, $this->type,"L'action $actionName a été executée sur le document");

		return true;
	}
}