<?php
class FournisseurFactureNotifieLiquidation extends ActionExecutor {
	
	public function go(){
		$id_e_fournisseur = $this->getDocumentEntite()->getEntiteWithRole($this->id_d, 'editeur');
		$actionCreator = $this->getActionCreator();
		$actionCreator->addAction($this->id_e,$this->id_u,$this->action, "La notification de la liquidation a �t� envoy� au fournisseur");
		$actionCreator->addToEntite($id_e_fournisseur,"La notification de liquidation a �t� envoy� par la collectivit�");
		$this->getNotificationMail()->notify($id_e_fournisseur,$this->id_d,$this->action,$this->type, "La facture a �t� liquider par la collectivite");
		$this->setLastMessage("La notification de la liquidation a �t� envoy� au fournisseur");
		return true;
	}
	
}