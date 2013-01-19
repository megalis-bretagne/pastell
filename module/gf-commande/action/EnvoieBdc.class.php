<?php 

class EnvoieBdc extends ActionExecutor {
	
	public function go(){
		$documentEntite = new DocumentEntite($this->getSQLQuery());
		$id_fournisseur = $documentEntite->getEntiteWithRole($this->id_d,"editeur");
			
		$actionCreator = $this->getActionCreator();
		$actionCreator->addAction($this->id_e,$this->id_u,$this->action, "Le bon de comande a �t� envoy�  au fournisseur");
		$actionCreator->addToEntite($id_fournisseur,"Le bon de commande a �t� envoy� ");
		
		$actionCreator->addAction($id_fournisseur,0,'reception-bdc', "Le  bon de commande a �t� re�u ");
		$actionCreator->addToEntite($this->id_e,"Le  bon de commande a �t� re�u");
		
	}
	
}