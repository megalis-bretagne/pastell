<?php

class AccepterFournisseur extends ActionExecutor {

	public function go(){
		$documentEntite = new DocumentEntite($this->getSQLQuery());
		$id_fournisseur = $documentEntite->getEntiteWithRole($this->id_d,"editeur");
	
		$entite = new Entite($this->getSQLQuery(),$id_fournisseur);
		$infoEntite = $entite->getInfo();
		$nomFournisseur = $infoEntite['denomination'];
		
		$entite = new Entite($this->getSQLQuery(),$this->id_e);
		$infoEntite = $entite->getInfo();
		$nomCol = $infoEntite['denomination'];
		
		$id_moderateur = $this->getIdModerateur();
		$this->getDocumentEntite()->addRole($this->id_d,$id_moderateur,"moderateur");
		
		
		$actionCreator = $this->getActionCreator();
		$actionCreator->addAction($this->id_e,$this->id_u,$this->action,"L'inscription de $nomFournisseur a �t� accept�");
		$actionCreator->addToEntite($id_fournisseur,"$nomCol a accept� l'inscription");
		$actionCreator->addToEntite($id_moderateur,"$nomCol a accept� l'inscription de $nomFournisseur");
		
		$action = "envoi-moderateur";
		$message = "Envoi de la demande � la mod�ration";
		$actionCreator = $this->getActionCreator();
		$actionCreator->addAction($id_moderateur,0,$action,$message);
		$actionCreator->addToEntite($id_fournisseur,$message);
		$actionCreator->addToEntite($this->id_e,$message);
		
		$this->setLastMessage("La demande a �t� envoy� � la mod�ration.");
		
		return true;			
	}
	
	private function getIdModerateur(){
		$validationFournisseur = $this->getGlobalConnecteur('validation-fournisseur');
		if (! $validationFournisseur) {
			throw new Exception("Aucun connecteur validation-fournisseur n'a �t� trouv�");
		}
		$id_e =  $validationFournisseur->getIdModerateur();
		if (! $id_e) {
			throw new Exception("Le connecteur validation-fournisseur n'est pas configur� correctement");
		}
		return $id_e;
	}
	
}