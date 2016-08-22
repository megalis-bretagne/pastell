<?php
class PastellControler extends Controler {

	public function _beforeAction(){
		if (! $this->getAuthentification()->isConnected()){
			$this->setLastError("Veuillez-vous authentifier pour accéder à cette page");
			$this->redirect("/Connexion/connexion");
		}
	}

	public function hasDroitEdition($id_e){
		$this->verifDroit($id_e,"entite:edition");
	}

	public function verifDroit($id_e,$droit,$redirect_to = ""){
		if ( $id_e && ! $this->getEntiteSQL()->getInfo($id_e)){
			$this->setLastError("L'entité $id_e n'existe pas");
			$this->redirect("/index.php");
		}
		if  (! $this->hasDroit($id_e,$droit)){
			$this->setLastError("Vous n'avez pas les droits nécessaires ($id_e:$droit) pour accéder à cette page");
			$this->redirect($redirect_to);
		}
		return true;
	}
	
	/**
	 * @return EntiteSQL
	 */
	public function getEntiteSQL(){
		return $this->getInstance('EntiteSQL');
	}
	
	public function hasDroit($id_e,$droit){
		return $this->getRoleUtilisateur()->hasDroit($this->getId_u(),$droit,$id_e);
	}
	
	/**
	 * @return RoleUtilisateur
	 */
	public function getRoleUtilisateur(){
		return $this->getInstance('RoleUtilisateur');
	}
	
	public function getId_u(){
		return $this->getAuthentification()->getId();
	}
	
	/**
	 * @return Authentification
	 */
	public function getAuthentification(){
		return $this->getInstance("Authentification");
	}
		
	public function hasDroitLecture($id_e){
		$this->verifDroit($id_e,"entite:lecture");
	}
	
	public function setNavigationInfo($id_e,$url){
		$listeCollectivite = $this->getRoleUtilisateur()->getEntite($this->getId_u(),"entite:lecture");
		$this->{'navigation_denomination'} = $this->getEntiteSQL()->getDenomination($id_e);
		$this->{'navigation_all_ancetre'} = $this->getEntiteSQL()->getAncetreNav($id_e,$listeCollectivite);
		$this->{'navigation_liste_fille'} = $this->getEntiteSQL()->getFilleInfoNavigation($id_e, $listeCollectivite);
		$this->{'navigation_entite_affiche_toutes'} = ($id_e != 0 && (count($listeCollectivite) > 1 ||($listeCollectivite && $listeCollectivite[0] == 0)));
		$this->{'navigation_url'} = $url;
	}
	
	public function renderDefault(){
		$this->setBreadcrumbs();
		$this->{'all_module'} = $this->getAllModule();
		$this->{'authentification'} = $this->getInstance("Authentification");
		$this->{'roleUtilisateur'} = $this->getRoleUtilisateur();
		$this->{'sqlQuery'} = $this->getSQLQuery();
		$this->{'objectInstancier'} = $this->getObjectInstancier();
		$this->{'timer'} = $this->getInstance('Timer');
		if (! $this->isViewParameter('menu_gauche_template')) {
			$this->{'menu_gauche_template'} = "DocumentMenuGauche";
			$this->{'menu_gauche_select'} = "";
			$this->{'menu_gauche_link'} = "Document/list?id_e={$this->id_e_menu}";
		}


		/** @var DaemonManager $daemonManager */
		$daemonManager = $this->getInstance('DaemonManager');
		
		if (
				$this->getRoleUtilisateur()->hasDroit($this->getId_u(),'system:lecture',0) &&
				$daemonManager->status()==DaemonManager::IS_STOPPED
		){
			$this->{'daemon_stopped_warning'} = true;
		} else {
			$this->{'daemon_stopped_warning'} = false;
		}
		
		parent::renderDefault();
	}

	/* Récupération des objets */

	public function setBreadcrumbs(){



		if (! $this->isViewParameter('id_e_menu')){
			$recuperateur = new Recuperateur($_GET);
			$this->{'id_e_menu'} = $recuperateur->getInt('id_e',0);
			$this->{'type_e_menu'} = $recuperateur->get('type',"");
		}

		$breadcrumbs = array();
		foreach( $this->getEntiteSQL()->getAncetre($this->{'id_e_menu'}) as $infoEntiteBR){
			$breadcrumbs[] = $infoEntiteBR['denomination'];
		}

		$listeCollectivite = $this->getRoleUtilisateur()->getEntite($this->getId_u(),"entite:lecture");

		$this->{'display_entite_racine'} = count($listeCollectivite) > 1 || (isset($listeCollectivite[0]) && $listeCollectivite[0] == 0);

		$this->{'navigation_all_ancetre'} = $this->getEntiteSQL()->getAncetreNav($this->{'id_e_menu'},$listeCollectivite);

		$this->{'navigation_denomination'} = $this->getEntiteSQL()->getDenomination($this->{'id_e_menu'});

		$this->{'breadcrumbs'} = $breadcrumbs;
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

	protected function getAPIController($controllerName){
		/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
		$baseAPIControllerFactory = $this->getInstance('BaseAPIControllerFactory');
		$instance = $baseAPIControllerFactory->getInstance($controllerName,$this->getId_u());
		$instance->setCallerType('console');
		return $instance;
	}

	/**
	 * @return SQLQuery
	 */
	public function getSQLQuery(){
		return $this->getInstance('SQLQuery');
	}

	/**
	 * @return DonneesFormulaireFactory
	 */
	public function getDonneesFormulaireFactory(){
		return $this->getInstance('DonneesFormulaireFactory');
	}

	/**
	 * @return ConnecteurEntiteSQL
	 */
	public function getConnecteurEntiteSQL(){
		return $this->getInstance('ConnecteurEntiteSQL');
	}

	/**
	 * @return WorkerSQL
	 */
	public function getWorkerSQL(){
		return $this->getInstance('WorkerSQL');
	}

	/**
	 * @return Journal
	 */
	public function getJournal(){
		return $this->getInstance('Journal');
	}

	/**
	 * @return DocumentTypeFactory
	 */
	public function getDocumentTypeFactory(){
		return $this->getInstance('DocumentTypeFactory');
	}

	/**
	 * @return ConnecteurFactory
	 */
	public function getConnecteurFactory(){
		return $this->getInstance('ConnecteurFactory');
	}

	/**
	 * @return Utilisateur
	 */
	public function getUtilisateur(){
		return $this->getInstance('Utilisateur');
	}

	/**
	 * @return UtilisateurListe
	 */
	public function getUtilisateurListe(){
		return $this->getInstance('UtilisateurListe');
	}

	/**
	 * @return ActionExecutorFactory
	 */
	public function getActionExecutorFactory(){
		return $this->getInstance('ActionExecutorFactory');
	}

	/**
	 * @return ActionPossible
	 */
	public function getActionPossible(){
		return $this->getInstance('ActionPossible');
	}

	/**
	 * @return Document
	 */
	public function getDocument(){
		return $this->getInstance('Document');
	}

	/**
	 * @return DocumentEntite
	 */
	public function getDocumentEntite(){
		return $this->getInstance('DocumentEntite');
	}

	/**
	 * @return ActionChange
	 */
	public function getActionChange(){
		return $this->getInstance('ActionChange');
	}

	/**
	 * @return RoleSQL
	 */
	public function getRoleSQL(){
		return $this->getInstance("RoleSQL");
	}

	/**
	 * @return EntiteListe
	 */
	public function getEntiteListe(){
		return $this->getInstance("EntiteListe");
	}

	/**
	 * @return ConnecteurDefinitionFiles
	 */
	public function getConnecteurDefinitionFiles(){
		return $this->getInstance("ConnecteurDefinitionFiles");
	}

	/**
	 * @return FluxEntiteSQL
	 */
	public function getFluxEntiteSQL(){
		return $this->getInstance("FluxEntiteSQL");
	}

	/**
	 * @return FluxDefinitionFiles
	 */
	public function getFluxDefinitionFiles(){
		return $this->getInstance("FluxDefinitionFiles");
	}

	/**
	 * @return ZenMail
	 */
	public function getZenMail(){
		return $this->getInstance("ZenMail");
	}

	/** @return UtilisateurCreator */
	public function getUtilisateurCreator(){
		return $this->getInstance('UtilisateurCreator');
	}

	/**
	 * @return ManifestFactory
	 */
	protected function getManifestFactory(){
		return $this->getInstance("ManifestFactory");
	}

	/**
	 * @return Extensions
	 */
	public function getExtensions(){
		return $this->getInstance("Extensions");
	}

	/**
	 * @return ExtensionSQL
	 */
	public function getExtensionSQL(){
		return $this->getInstance("ExtensionSQL");
	}

}