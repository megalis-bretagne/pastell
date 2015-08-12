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
		$this->connecteur_info = $this->getFluxEntiteSQL()->getConnecteur($this->id_e,$this->flux,$this->type_connecteur);
		
		
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
		$connecteur_disponible = $this->ConnecteurDisponible->getList($this->getId_u(),$id_e,$type_connecteur); 
		
		$this->ConnecteurEntiteSQL->getDisponible($id_e,$type_connecteur);
		if (! $connecteur_disponible){
			$this->LastError->setLastError("Aucun connecteur « $type_connecteur » disponible !");
			$this->redirect("/entite/detail.php?id_e=$id_e&page=".self::FLUX_NUM_ONGLET);
		} // @codeCoverageIgnore
				
		return $connecteur_disponible;
	}
	    
	public function doEditionModif(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$flux = $recuperateur->get('flux');
		$type = $recuperateur->get('type');
		$id_ce = $recuperateur->getInt('id_ce');
		
		$this->hasDroitEdition($id_e);
		try {
			if ($id_ce){
				$this->hasGoodType($id_ce, $type);
				$this->editionModif($id_e, $flux, $type, $id_ce);
				$this->LastMessage->setLastMessage("Connecteur associé au flux avec succès");
			} else {
				$this->FluxEntiteSQL->deleteConnecteur($id_e,$flux,$type);
				$this->LastMessage->setLastMessage("Connecteur déselectionné avec succès");				
			}
		} catch (Exception $ex) {
			$this->LastError->setLastError($ex->getMessage());
		}           
		$this->redirect("/entite/detail.php?id_e=$id_e&page=".self::FLUX_NUM_ONGLET);
		
	}  // @codeCoverageIgnore            
	
	private function hasGoodType($id_ce,$type){
		$info = $this->ConnecteurEntiteSQL->getInfo($id_ce);
		if ($info['type'] != $type){
			throw new Exception("Le connecteur n'est pas du bon type.");
		}
	}
	
	public function editionModif($id_e, $flux, $type, $id_ce) {
		$this->hasGoodType($id_ce, $type);
		
		$info = $this->ConnecteurEntiteSQL->getInfo($id_ce);
		$this->hasDroitEdition($info['id_e']);
		
		if ($flux!=null) {
			$info = $this->FluxDefinitionFiles->getInfo($flux);
			if (!$info) {
				throw new Exception("Le type de flux n'existe pas.");
			}              
		}
		$id_fe = $this->FluxEntiteSQL->addConnecteur($id_e,$flux,$type,$id_ce);
		return $id_fe;
	}

	public function getListFlux($id_e){
		
		$all_flux_entite = $this->FluxEntiteHeritageSQL->getAll($id_e);
		foreach($this->FluxDefinitionFiles->getAll() as $id_flux => $flux_definition){
			$documentType = $this->DocumentTypeFactory->getFluxDocumentType($id_flux);
			foreach($documentType->getConnecteur() as $j=>$connecteur_type) {
				$line = array();
				$line['nb_connecteur'] = count($documentType->getConnecteur());
				$line['num_connecteur'] = $j;
				$line['id_flux'] = $id_flux;
				$line['nom_flux'] = $documentType->getName();
				$line['connecteur_type'] = $connecteur_type;
				$line['inherited_flux'] = false;
				if (isset($all_flux_entite[$id_flux][$connecteur_type])){
					$line['connecteur_info'] = $all_flux_entite[$id_flux][$connecteur_type];
				} else {
					$line['connecteur_info'] = false;
						
				}
				if (isset($all_flux_entite[$id_flux]['inherited_flux'])){
					$line['inherited_flux'] = $all_flux_entite[$id_flux]['inherited_flux'];
				}
				
				$result[] = $line;
			}
		}
		return $result;
	}
	
	public function toogleHeritageAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$flux = $recuperateur->get('flux');
		$this->hasDroitEdition($id_e);
		$this->FluxEntiteHeritageSQL->toogleInheritance($id_e,$flux);
		$this->LastMessage->setLastMessage("L'héritage a été modifié");
		$this->redirect("/entite/detail.php?id_e=$id_e&page=".self::FLUX_NUM_ONGLET);
	} // @codeCoverageIgnore
	
	
}