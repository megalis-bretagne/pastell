<?php
class FluxControler extends PastellControler {
	
	const FLUX_NUM_ONGLET = 4;
	
	public function editionAction(){
		$recuperateur = new Recuperateur($_GET);
		$this->id_e = $recuperateur->getInt('id_e');
		$this->flux = $recuperateur->get('flux');
		$this->type_connecteur = $recuperateur->get('type');
		
		$this->hasDroitEdition($this->id_e);
		$this->entite_denomination = $this->EntiteSQL->getDenomination($this->id_e);
		
		$this->connecteur_disponible = $this->getConnecteurDispo($this->id_e,$this->type_connecteur);	
		$this->id_ce = $this->getFluxEntiteSQL()->getConnecteurId($this->id_e,$this->flux,$this->type_connecteur);
		
		if ($this->flux){
			$this->flux_name = $this->DocumentTypeFactory->getFluxDocumentType($this->flux)->getName() ;
		} else {
			$this->flux_name = "global";
		}
		
		$this->page_title = "{$this->entite_denomination} : Association d'un connecteur et d'un flux";
		$this->template_milieu = "FluxEdition";
		$this->renderDefault();
	}
	
	/**
	 * @return FluxEntiteSQL
	 */
	private function getFluxEntiteSQL(){
		return $this->FluxEntiteSQL;
	}
	
	private function getConnecteurDispo($id_e,$type_connecteur){
		$connecteur_disponible = $this->ConnecteurEntiteSQL->getDisponible($id_e,$type_connecteur);
		if (! $connecteur_disponible){
			$this->LastError->setLastError("Aucun connecteur « $type_connecteur » disponible !");
			$this->redirect("/entite/detail.php?id_e=$id_e&page=".self::FLUX_NUM_ONGLET);
		}
		
		return $connecteur_disponible;
	}
	    
	public function doEditionModif(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$flux = $recuperateur->get('flux');
		$type = $recuperateur->get('type');
		$id_ce = $recuperateur->get('id_ce');

		$this->hasDroitEdition($id_e);
		try {
			$this->hasGoodType($id_ce, $type);
			$this->editionModif($id_e, $flux, $type, $id_ce);
			$this->LastMessage->setLastMessage("Connecteur associé au flux avec succès");
		} catch (Exception $ex) {
			$this->LastError->setLastError($ex->getMessage());
		}           
		$this->redirect("/entite/detail.php?id_e=$id_e&page=".self::FLUX_NUM_ONGLET);
		
	}                
	
	private function hasGoodType($id_ce,$type){
		$info = $this->ConnecteurEntiteSQL->getInfo($id_ce);
		if ($info['type'] != $type){
			throw new Exception("Le connecteur n'est pas du bon type.");
		}
	}
	
	public function editionModif($id_e, $flux, $type, $id_ce) {
		$this->hasGoodType($id_ce, $type);
		if ($flux!=null) {
			$info = $this->FluxDefinitionFiles->getInfo($flux);
			if (!$info) {
				throw new Exception("Le type de flux n'existe pas.");
			}              
		}
		$id_fe = $this->FluxEntiteSQL->addConnecteur($id_e,$flux,$type,$id_ce);
		return $id_fe;
	}

        
	public function doSupprimer(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$flux = $recuperateur->get('flux');
		$type = $recuperateur->get('type');
		$this->hasDroitEdition($id_e);
		$this->FluxEntiteSQL->deleteConnecteur($id_e,$flux,$type);
		$this->LastMessage->setLastMessage("L'association a été supprimée");
		$this->redirect("/entite/detail.php?id_e=$id_e&page=".self::FLUX_NUM_ONGLET);
	}
	
	
}