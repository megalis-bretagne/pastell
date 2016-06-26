<?php
class PastellControler extends Controler {

	protected function getAPIController($controllerName){
		/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
		$baseAPIControllerFactory = $this->getObjectInstancier()->getInstance('BaseAPIControllerFactory');
		return $baseAPIControllerFactory->getInstance($controllerName,$this->Authentification->getId());
	}

	public function getId_u(){
		return $this->Authentification->getId();
	}
	
	public function hasDroit($id_e,$droit){
		return $this->RoleUtilisateur->hasDroit($this->getId_u(),$droit,$id_e);
	}
	
	public function verifDroit($id_e,$droit,$redirect_to = ""){
		if ( $id_e && ! $this->EntiteSQL->getInfo($id_e)){
			$this->LastError->setLastError("L'entité $id_e n'existe pas");
			$this->redirect("/index.php");
		}
		if  ($this->hasDroit($id_e,$droit)){
			return true;
		}
		$this->LastError->setLastError("Vous n'avez pas les droits nécessaires ($id_e:$droit) pour accéder à cette page");
		$this->redirect($redirect_to);
	}
	
	public function hasDroitEdition($id_e){
		$this->verifDroit($id_e,"entite:edition");
	}
	
	public function hasDroitLecture($id_e){
		$this->verifDroit($id_e,"entite:lecture");
	}
	
	public function setNavigationInfo($id_e,$url){
		$listeCollectivite = $this->RoleUtilisateur->getEntite($this->getId_u(),"entite:lecture");
		$this->navigation_denomination = $this->EntiteSQL->getDenomination($id_e);
		$this->navigation_all_ancetre = $this->EntiteSQL->getAncetreNav($id_e,$listeCollectivite);
		$this->navigation_liste_fille = $this->EntiteSQL->getFilleInfoNavigation($id_e, $listeCollectivite);
		$this->navigation_entite_affiche_toutes = ($id_e != 0 && (count($listeCollectivite) > 1 ||($listeCollectivite && $listeCollectivite[0] == 0)));
		$this->navigation_url = $url;
	}
		
	public function setBreadcrumbs(){
		if (! $this->isViewParameter('id_e_menu')){
			$recuperateur = new Recuperateur($_GET);
			$this->id_e_menu = $recuperateur->getInt('id_e',0);
			$this->type_e_menu = $recuperateur->get('type',"");
		}
		
		$breadcrumbs = array();
		foreach( $this->EntiteSQL->getAncetre($this->id_e_menu) as $infoEntiteBR){
			$breadcrumbs[] = $infoEntiteBR['denomination'];
		}
		$this->breadcrumbs = $breadcrumbs;
	}	

	
	public function getAllModule(){
		$all_module = array();

		/** @var DocumentTypeAPIController $documentTypeController */
		$documentTypeController = $this->getAPIController('DocumentType');
		$list = $documentTypeController->listAction();

		foreach($list as $flux_id => $flux_info){
			$all_module[$flux_info['type']][$flux_id]  = $flux_info['nom'];
		}

		return $all_module;
	}
	
	public function renderDefault(){
		$this->setBreadcrumbs();
		$this->all_module = $this->getAllModule();
		$this->authentification = $this->Authentification;
		$this->roleUtilisateur = $this->RoleUtilisateur;
		$this->sqlQuery = $this->SQLQuery;
		$this->objectInstancier = $this->ObjectInstancier;
		$this->timer = $this->Timer;
		
		if ($this->RoleUtilisateur->hasDroit($this->Authentification->getId(),'system:lecture',0) && $this->DaemonManager->status()==DaemonManager::IS_STOPPED){
			$this->daemon_stopped_warning = true;
		} else {
			$this->daemon_stopped_warning = false;
		}
		
		parent::renderDefault();
	}

	/**
	 * @return SQLQuery
	 */
	public function getSQLQuery(){
		return $this->{'SQLQuery'};
	}
	
	/**
	 * @return DonneesFormulaireFactory
	 */
	public function getDonneesFormulaireFactory(){
		return $this->{'DonneesFormulaireFactory'};
	}


	/**
	 * @return ConnecteurEntiteSQL
	 */
	public function getConnecteurEntiteSQL(){
		return $this->{'ConnecteurEntiteSQL'};
	}

	/**
	 * @return EntiteSQL
	 */
	public function getEntiteSQL(){
		return $this->{'EntiteSQL'};
	}

	/**
	 * @return LastMessage
	 */
	public function getLastMessage(){
		return $this->{'LastMessage'};
	}

	/**
	 * @return LastError
	 */
	public function getLastError(){
		return $this->{'LastError'};
	}

	/**
	 * @return Journal
	 */
	public function getJournal(){
		return $this->{'Journal'};
	}

	/**
	 * @return RoleUtilisateur
	 */
	public function getRoleUtilisateur(){
		return $this->{'RoleUtilisateur'};
	}

	/**
	 * @return DocumentTypeFactory
	 */
	public function getDocumentTypeFactory(){
		return $this->{'DocumentTypeFactory'};
	}

}