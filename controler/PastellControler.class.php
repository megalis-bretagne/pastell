<?php
class PastellControler extends Controler {

	public function _beforeAction(){
		if (! $this->getAuthentification()->isConnected()){

		    $request_uri = $_SERVER['REQUEST_URI'];

		    if ($this->getGetInfo()->get(FrontController::PAGE_REQUEST)) {
                $this->setLastError("Veuillez saisir vos identifiants de connexion pour accéder à cette page.");
            }
			$this->redirect("/Connexion/connexion?request_uri=".urlencode($request_uri));
		}
	}

	public function hasDroitLecture($id_e){
		$this->verifDroit($id_e,"entite:lecture");
	}

	public function hasDroitEdition($id_e){
		$this->verifDroit($id_e,"entite:edition");
	}

	public function verifDroit($id_e,$droit,$redirect_to = ""){
		if ( $id_e && ! $this->getEntiteSQL()->getInfo($id_e)){
		    if ($this->hasDroit(0,$droit)) {
		        return true;
            }
            $this->setLastError("L'entité $id_e n'existe pas");
            $this->redirect("/index.php");

		}

		if  (! $this->hasDroit($id_e,$droit)){
			$this->setLastError("Vous n'avez pas les droits nécessaires ($id_e:$droit) pour accéder à cette page");
			$this->redirect($redirect_to);
		}
		return true;
	}

	public function hasDroit($id_e,$droit){
		if (! $this->getId_u()){
			return true;
		}
		return $this->getRoleUtilisateur()->hasDroit($this->getId_u(),$droit,$id_e);
	}

	public function getId_u(){
		return $this->getAuthentification()->getId();
	}

	public function setNavigationInfo($id_e,$url){
		$listeCollectivite = $this->getRoleUtilisateur()->getEntite($this->getId_u(),"entite:lecture");
		$this->{'navigation_denomination'} = $this->getEntiteSQL()->getDenomination($id_e);
		$this->{'navigation_all_ancetre'} = $this->getEntiteSQL()->getAncetreNav($id_e,$listeCollectivite);
		$this->{'navigation_liste_fille'} = $this->getEntiteSQL()->getFilleInfoNavigation($id_e, $listeCollectivite);
		$this->{'navigation_entite_affiche_toutes'} = ($id_e != 0 && (count($listeCollectivite) > 1 ||($listeCollectivite && $listeCollectivite[0] == 0)));
		$this->{'navigation_url'} = $url;
	}

	public function render($template) {
		$this->{'sqlQuery'} = $this->getSQLQuery();
		$this->{'objectInstancier'} = $this->getObjectInstancier();
		$this->{'manifest_info'} = $this->getManifestFactory()
			->getPastellManifest()
			->getInfo();
		parent::render($template);
	}

	/**
	 * @throws NotFoundException
	 */
	public function renderDefault(){
		$this->setBreadcrumbs();
		$this->{'all_module'} = $this->getAllModule();
		$this->{'authentification'} = $this->getInstance("Authentification");
		$this->{'roleUtilisateur'} = $this->getRoleUtilisateur();
		$this->{'sqlQuery'} = $this->getSQLQuery();
		$this->{'objectInstancier'} = $this->getObjectInstancier();
		$this->{'manifest_info'} = $this->getManifestFactory()->getPastellManifest()->getInfo();

		$this->{'timer'} = $this->getInstance('PastellTimer');
		if (! $this->isViewParameter('menu_gauche_template')) {
			$this->{'menu_gauche_template'} = "DocumentMenuGauche";
			$this->{'menu_gauche_select'} = "";
			$this->{'menu_gauche_link'} = "Document/list?id_e=".$this->{'id_e_menu'};
		}
		if (! $this->isViewParameter('navigation_url')){
			$this->{'navigation_url'} = "Document/index";
		}

		/** @var DaemonManager $daemonManager */
		$daemonManager = $this->getInstance('DaemonManager');
		
		if (
				$this->getRoleUtilisateur()->hasDroit($this->getId_u(),'system:lecture',0)
		) {

            $this->{'nb_job_lock'} = $this->getObjectInstancier()
				->getInstance(JobQueueSQL::class)
				->getNbLockSinceOneHour();

            if ($daemonManager->status() == DaemonManager::IS_STOPPED) {
                $this->{'daemon_stopped_warning'} = true;

            } else {
                $this->{'daemon_stopped_warning'} = false;
            }
        }

		parent::renderDefault();
	}

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

		$this->{'display_entite_racine'} =  $this->{'id_e_menu'} !=0 && (count($listeCollectivite) > 1 || (isset($listeCollectivite[0]) && $listeCollectivite[0] == 0));

		$this->{'navigation_all_ancetre'} = $this->getEntiteSQL()->getAncetreNav($this->{'id_e_menu'},$listeCollectivite);

		$this->{'navigation_denomination'} = $this->getEntiteSQL()->getDenomination($this->{'id_e_menu'});

		$this->{'breadcrumbs'} = $breadcrumbs;
		
	}

	/**
	 * @return array
	 * @throws NotFoundException
	 */
	public function getAllModule(){
		$all_module = array();

		/** @var FluxAPIController $fluxAPIController */
		$fluxAPIController = $this->getAPIController('Flux');
		$list = $fluxAPIController->get();

		foreach($list as $flux_id => $flux_info){
			$all_module[$flux_info['type']][$flux_id]  = $flux_info['nom'];
		}

		setlocale(LC_COLLATE, 'fr_FR.utf8');
        ksort($all_module, SORT_LOCALE_STRING );
		return $all_module;
	}

	/**
	 * @param $controllerName
	 * @return BaseAPIController
	 * @throws NotFoundException
	 */
	protected function getAPIController($controllerName){
		/** @var BaseAPIControllerFactory $baseAPIControllerFactory */
		$baseAPIControllerFactory = $this->getInstance('BaseAPIControllerFactory');
		$instance = $baseAPIControllerFactory->getInstance($controllerName,$this->getId_u());
		$instance->setCallerType('console');
		return $instance;
	}

	/** @var  InternalAPI */
	private $internalAPI;

	public function apiCall($method,$ressource,$data){
		if (! $this->internalAPI) {
			$this->internalAPI = $this->getObjectInstancier()->getInstance("InternalAPI");
			$this->internalAPI->setCallerType(InternalAPI::CALLER_TYPE_CONSOLE);
			$this->internalAPI->setFileUploader($this->getObjectInstancier()->getInstance("FileUploader"));
			$this->internalAPI->setUtilisateurId($this->getId_u());
		}
		return $this->internalAPI->$method($ressource,$data);
	}

	protected function apiGet($ressource){
		return $this->apiCall('get',$ressource,$this->getGetInfo()->getAll());
	}

	protected function apiPost($ressource){
		return $this->apiCall('post',$ressource,$this->getPostInfo()->getAll());
	}

	protected function apiDelete($ressource){
		return $this->apiCall('delete',$ressource,array());
	}

	protected function apiPatch($ressource){
		return $this->apiCall('patch',$ressource,$this->getPostInfo()->getAll());
	}

	/* Récupération des objets */

	/**
	 * @return SQLQuery
	 */
	public function getSQLQuery(){
		return $this->getInstance('SQLQuery');
	}

	/**
	 * @return EntiteSQL
	 */
	public function getEntiteSQL(){
		return $this->getInstance('EntiteSQL');
	}

	/**
	 * @return RoleUtilisateur
	 */
	public function getRoleUtilisateur(){
		return $this->getInstance('RoleUtilisateur');
	}

	/**
	 * @return Authentification
	 */
	public function getAuthentification(){
		return $this->getInstance("Authentification");
	}

	public function getDonneesFormulaireFactory(){
		return $this->getObjectInstancier()->getInstance(DonneesFormulaireFactory::class);
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

	public function getManifestFactory(){
		return $this->getInstance(ManifestFactory::class);
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

    /**
     * @return Monolog\Logger
     */
	public function getLogger(){
	    return $this->getInstance("Monolog\Logger");
    }

}