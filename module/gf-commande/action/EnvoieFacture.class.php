<?php 

class EnvoieFacture extends ActionExecutor {
	
	public function go(){
		$documentEntite = new DocumentEntite($this->getSQLQuery());
		$id_col = $documentEntite->getEntiteWithRole($this->id_d,"lecteur");
			
		$actionCreator = $this->getActionCreator();
		$actionCreator->addAction($this->id_e,$this->id_u,$this->action, "La facture a �t� envoy�e � la collectivit�");
		$actionCreator->addToEntite($id_col,"La facture a �t� envoy� ");
		
		$actionCreator->addAction($id_col,0,'reception-facture', "La facture a �t� re�ue ");
		$actionCreator->addToEntite($this->id_e,"La facture a �t� re�ue");
		
	}
	
}