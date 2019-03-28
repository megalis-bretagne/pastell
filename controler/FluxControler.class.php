<?php
class FluxControler extends PastellControler {

	public function _beforeAction() {
		parent::_beforeAction();
        $id_e = $this->getPostOrGetInfo()->getInt('id_e');

        $this->hasDroitLecture($id_e);
		$this->setNavigationInfo($id_e,"Flux/index?");
		$this->{'menu_gauche_template'} = "EntiteMenuGauche";
		$this->{'menu_gauche_select'} = "Flux/index";
	}

	const FLUX_NUM_ONGLET = 4;


	public function indexAction(){
		$recuperateur = new Recuperateur($_POST);
		$id_e = $recuperateur->getInt('id_e');
		$this->hasDroitLecture($id_e);
		$this->{'id_e'}= $id_e;

		if ($id_e){
			/** @var FluxEntiteHeritageSQL $fluxEntiteHeritageSQL */
			$fluxEntiteHeritageSQL = $this->getInstance("FluxEntiteHeritageSQL");

			/** @var FluxControler $fluxControler */
			$fluxControler = $this->getInstance("FluxControler");

			$this->{'id_e_mere'}= $this->getEntiteSQL()->getEntiteMere($id_e);
			$this->{'all_herited'} = $fluxEntiteHeritageSQL->hasInheritanceAllFlux($id_e);
			$this->{'flux_connecteur_list'}= $fluxControler->getListFlux($id_e);
			$this->{'template_milieu'}= "FluxList";
		} else {
			$all_connecteur_type = $this->getConnecteurDefinitionFiles()->getAllGlobalType();
			$all_type = array();
			foreach($all_connecteur_type as $connecteur_type){
				try {
					$global_connecteur = $this->getConnecteurFactory()->getGlobalConnecteur($connecteur_type);
				} catch (Exception $e){
					$global_connecteur =  false;
				}
				$all_type[$connecteur_type] = $global_connecteur;
			}

			$this->{'all_connecteur_type'}= $all_type;
			$this->{'all_flux_entite'}= $this->getFluxEntiteSQL()->getAll($id_e);
			if (isset($this->{'all_flux_entite'}['global'])){
				$this->{'all_flux_global'}= $this->{'all_flux_entite'}['global'];
			} else {
				$this->{'all_flux_global'}= array();
			}
			$this->{'template_milieu'}= "FluxGlobalList";
		}
		$this->setNavigationInfo($id_e,"Flux/index?");
		$this->{'menu_gauche_select'} = "Flux/index";
		$this->{'entite_denomination'}= $this->getEntiteSQL()->getDenomination($this->{'id_e'});
		$this->{'page_title'}= "{$this->{'entite_denomination'}} : Liste des flux";

		$this->renderDefault();
	}

	public function editionAction(){
		$this->{'id_e'}= $this->getGetInfo()->getInt('id_e');
		$this->{'flux'}= $this->getGetInfo()->get('flux','');
		$this->{'type_connecteur'}= $this->getGetInfo()->get('type');
		
		$this->hasDroitEdition($this->{'id_e'});
		$this->{'entite_denomination'}= $this->getEntiteSQL()->getDenomination($this->{'id_e'});
		
		$this->{'connecteur_disponible'}= $this->getConnecteurDispo($this->{'id_e'},$this->{'type_connecteur'});
		$this->{'connecteur_info'}= $this->getFluxEntiteSQL()->getConnecteur($this->{'id_e'},$this->{'flux'},$this->{'type_connecteur'});
		
		
		if ($this->{'flux'}){
			$this->{'flux_name'}= $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'flux'})->getName() ;
		} else {
			$this->{'flux_name'}= "global";
		}
		
		$this->{'page_title'}= "{$this->{'entite_denomination'}} : Association d'un connecteur et d'un flux";
		$this->{'template_milieu'}= "FluxEdition";
		$this->renderDefault();
	}

	private function getConnecteurDispo($id_e,$type_connecteur){
		/** @var ConnecteurDisponible $connecteurDisponible */
		$connecteurDisponible = $this->getInstance("ConnecteurDisponible");

		$connecteur_disponible = $connecteurDisponible->getList($this->getId_u(),$id_e,$type_connecteur);
		
		$this->getConnecteurEntiteSQL()->getDisponible($id_e,$type_connecteur);
		if (! $connecteur_disponible){
			$this->setLastError("Aucun connecteur « $type_connecteur » disponible !");
			$this->redirect("/Flux/index?id_e=$id_e");
		} // @codeCoverageIgnore
				
		return $connecteur_disponible;
	}
	    
	public function doEditionAction(){
		$id_e = $this->getPostInfo()->getInt('id_e');
		$flux = $this->getPostInfo()->get('flux');
		$type = $this->getPostInfo()->get('type');
		$id_ce = $this->getPostInfo()->getInt('id_ce');
		
		$this->hasDroitEdition($id_e);
		try {
			if ($id_ce){
				$this->hasGoodType($id_ce, $type);
				$this->editionModif($id_e, $flux, $type, $id_ce);
				$this->setLastMessage("Connecteur associé au flux avec succès");
			} else {
				$this->getFluxEntiteSQL()->deleteConnecteur($id_e,$flux,$type);
				$this->setLastMessage("Connecteur désélectionné avec succès");
			}
		} catch (Exception $ex) {
			$this->setLastError($ex->getMessage());
		}           
		$this->redirect("/Flux/index?id_e=$id_e");
		
	}  // @codeCoverageIgnore            
	
	private function hasGoodType($id_ce,$type){
		$info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
		if ($info['type'] != $type){
			throw new Exception("Le connecteur n'est pas du bon type :  {$info['type']} présenté, $type requis");
		}
	}
	
	public function editionModif($id_e, $flux, $type, $id_ce) {
		$this->hasGoodType($id_ce, $type);

		$info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
		$this->hasDroitEdition($info['id_e']);

		if ($flux!=null) {
			$info =$this->getFluxDefinitionFiles()->getInfo($flux);
			if (!$info) {
				throw new Exception("Le type de flux n'existe pas.");
			}              
		}
		$id_fe = $this->getFluxEntiteSQL()->addConnecteur($id_e,$flux,$type,$id_ce);
		return $id_fe;
	}

	public function getListFlux($id_e){
		$result = array();
		/** @var FluxEntiteHeritageSQL $fluxEntiteHeritageSQL */
		$fluxEntiteHeritageSQL = $this->getInstance("FluxEntiteHeritageSQL");

		$all_flux_entite = $fluxEntiteHeritageSQL->getAll($id_e);
		foreach($this->getFluxDefinitionFiles()->getAll() as $id_flux => $flux_definition){
			$documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id_flux);

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
		/** @var FluxEntiteHeritageSQL $fluxEntiteHeritageSQL */
		$fluxEntiteHeritageSQL = $this->getInstance("FluxEntiteHeritageSQL");

		$id_e = $this->getPostInfo()->getInt('id_e');
		$flux = $this->getPostInfo()->get('flux');
		$this->hasDroitEdition($id_e);
		$fluxEntiteHeritageSQL->toogleInheritance($id_e,$flux);
		$this->setLastMessage("L'héritage a été modifié");
		$this->redirect("/Flux/index?id_e=$id_e");
	} // @codeCoverageIgnore
	
	
}