<?php 



class WebGFCStatus extends ActionExecutor {

	
	public function go(){
		$webgfc_courrier_id = $this->getDonneesFormulaire()->get('webgfc_courrier_id');
		
		
		$webGFC = $this->getConnecteur('GFC');
		$result = $webGFC->getStatus($webgfc_courrier_id);
		
		$this->setLastMessage($webGFC->getLastMessage());
		
		if ($result == "OK"){
			
			$documentEntite = new DocumentEntite($this->getSQLQuery());
			
			$id_col = $documentEntite->getEntiteWithRole($this->id_d,"lecteur");
			
			$actionCreator = $this->getActionCreator();
			
			$actionCreator->addAction($this->id_e,$this->id_u,'traite-webgfc', "Le document a �t� trait� ");
			$actionCreator->addToEntite($id_col,"Le document a �t� trait�");
		}
		
		return true;
	}
	
}