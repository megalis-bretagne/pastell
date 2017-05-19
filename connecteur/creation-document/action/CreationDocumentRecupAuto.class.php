<?php

class CreationDocumentRecupAuto extends ActionExecutor {

	public function go(){
		$connecteur = $this->getMyConnecteur();
		
		try{
			$result = $connecteur->recupAllAuto($this->id_e);
			if ($result){
				$this->setLastMessage(implode("<br/>",$result));
			} else {
				$this->setLastMessage("Aucun fichier trouvé");
			}
		} catch (Exception $e){
			$this->setModeAuto(0);
			$this->setLastMessage("Erreur lors de l'importation : ".$e->getMessage()."<br />\n"."La récupération automatique passe à 'non'");
			return false;
		}
		return true;
	}
		
	private function setModeAuto($mode_auto) {
		$connecteur_properties = $this->getConnecteurProperties();
		$connecteur_properties->setData('connecteur_auto',$mode_auto);
	}
		
}