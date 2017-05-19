<?php 

class RecupPESRetourAll extends ActionExecutor {
	
	public function go(){

		$entiteListe = new EntiteListe($this->getSQLQuery());
		
		$all_col = $entiteListe->getAll(Entite::TYPE_COLLECTIVITE);
		$all_col =  array_merge($all_col,$entiteListe->getAll(Entite::TYPE_CENTRE_DE_GESTION));
		
		$envoye = array();
		foreach($all_col as $infoCollectivite) {			
			try {
				$tdT = $this->objectInstancier->ConnecteurFactory->getConnecteurByType($infoCollectivite['id_e'],'helios-pes-retour','TdT');
				if (!$tdT){
					continue;
				}
				$tdT->getPESRetourListe();
				$envoye[] = "{$infoCollectivite['denomination']}  : Les fichiers Hélios PES Retour ont été récupérés";
			} catch(Exception $e ){
				$envoye[] = "{$infoCollectivite['denomination']}  : ".($e->getMessage());
				continue;
			}
		}
		
		$this->setLastMessage("Résultat :<br/>".implode("<br/>",$envoye));
		return true;
	}
	
}