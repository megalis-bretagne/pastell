<?php

class AccepterFournisseur extends ActionExecutor {

	public function go(){
		$documentEntite = new DocumentEntite($this->getSQLQuery());
		$id_fournisseur = $documentEntite->getEntiteWithRole($this->id_d,"editeur");
		
		/*$entiteRelation = new EntiteRelation($this->getSQLQuery());
		$entiteRelation->addRelation($id_fournisseur,EntiteRelation::IS_FOURNISSEUR,$this->id_e);
		*/
		
		$entite = new Entite($this->getSQLQuery(),$id_fournisseur);
		$infoEntite = $entite->getInfo();
		$nomFournisseur = $infoEntite['denomination'];
		
		$entite = new Entite($this->getSQLQuery(),$this->id_e);
		$infoEntite = $entite->getInfo();
		$nomCol = $infoEntite['denomination'];
		
		$actionCreator = $this->getActionCreator();
		
		$actionCreator->addAction($this->id_e,$this->id_u,$this->action,"L'inscription de $nomFournisseur a �t� accept�");
		$actionCreator->addToEntite($id_fournisseur,"$nomCol a accept� l'inscription");
		
		$this->setLastMessage("Le fournisseur a �t� accept�.");
		return true;			
	}
}