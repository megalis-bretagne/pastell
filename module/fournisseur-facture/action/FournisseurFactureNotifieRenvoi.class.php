<?php 

class FournisseurFactureNotifieRenvoi extends ActionExecutor {
	
	
	public function go(){
		$id_e_fournisseur = $this->getDocumentEntite()->getEntiteWithRole($this->id_d, 'editeur');
		
		$actionCreator = $this->getActionCreator();
		$actionCreator->addAction($this->id_e,$this->id_u,$this->action, "La notification de renvoi a �t� envoy� au fournisseur");
		$actionCreator->addToEntite($id_e_fournisseur,"La notification de renvoi a �t� envoy� par la collectivit�");
		
		$this->getNotificationMail()->notify($id_e_fournisseur,$this->id_d,$this->action,$this->type, "La facture a �t� renvoy� par la collectivite");

		$this->setLastMessage("La facture a �t� renvoy� au fournisseur");
		return true;
	}
	
}