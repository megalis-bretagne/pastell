<?php


abstract class ActionExecutor {
	
	protected $id_d;
	protected $id_e;
	protected $id_u;
	protected $action;
	protected $id_destinataire;
	protected $from_api;
	protected $id_ce;
	protected $type;
    protected $action_params;
    protected $id_worker;
	
	protected $objectInstancier;
	/** @var  DonneesFormulaire */
	private $docDonneesFormulaire;
	private $connecteurs;
	private $connecteurConfigs;
	
	private $lastMessage; 
	private $lastMessageString;

	public function __construct(ObjectInstancier $objectInstancier){
		$this->objectInstancier = $objectInstancier;
	}
	
	public function setEntiteId($id_e){
		$this->id_e = $id_e;	
	}
	
	public function setUtilisateurId($id_u){
		$this->id_u = $id_u;
	}
		
	public function setAction($action_name){
		$this->action = $action_name;
	}
		
	public function setConnecteurId($type, $id_ce){
		$this->id_ce = $id_ce;
		$this->type = $type;
	}
	
	public function setDocumentId($type, $id_d){
		$this->id_d = $id_d;
		$this->type = $type;
	}
	
	public function clearCache(){
		$this->connecteurs = false;
		$this->docDonneesFormulaire = false;
		$this->connecteurConfig = false;
	}
	
	public function setDestinataireId(array $id_destinataire){
		$this->id_destinataire = $id_destinataire;	
	}
	
	public function setActionParams(array $action_params) {
		$this->action_params = $action_params;
	}
        
	public function setFromApi($from_api){
		$this->from_api = $from_api;
	}
	
	public function setIdWorker($id_worker){
		$this->id_worker = $id_worker;
		
	}
	
	public function getLastMessage(){
		return $this->lastMessage;
	}
	
	public function setLastMessage($message){
		$this->lastMessage = $message;
	}
	
	public function getLastMessageString() {
		return $this->lastMessageString;
	}

	public function setLastMessageString($message) {
		$this->lastMessageString = $message;
	}
	
	/**
	 * @return ActionCreator
	 */
	public function getActionCreator($id_d = false){
		if (! $id_d){
			$id_d = $this->id_d;
		}
		return new ActionCreator($this->getSQLQuery(),$this->getJournal(),$id_d);	
	}
	
	/**
	 * @return DonneesFormulaire
	 */
	public function getDonneesFormulaire(){
		if (!$this->docDonneesFormulaire) {
			$this->docDonneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        }
        return $this->docDonneesFormulaire;
	}
	
	/**
	 * Permet de récupérer l'objet Formulaire configuré pour ce DonneesFormulaire
	 * @return Formulaire
	 */
	public function getFormulaire(){
		return $this->docDonneesFormulaire->getFormulaire();
	}
	
	/**
	 * @return Journal
	 */
	public function getJournal(){
		return $this->objectInstancier->Journal;
	}
	
	/**
	 * @return ZenMail
	 */
	public function getZenMail(){
		return $this->objectInstancier->ZenMail;
	}
	
	/**
	 * @return DonneesFormulaireFactory
	 */
	public function getDonneesFormulaireFactory(){
		return $this->objectInstancier->DonneesFormulaireFactory;
	}
	
	/**
	 * @return DocumentEntite
	 */
	public function getDocumentEntite(){
		return $this->objectInstancier->DocumentEntite;
	}

	/**
	 * @return Document
	 */
	public function getDocument(){
		return $this->objectInstancier->Document;
	}
	
	/**
	 * @return DocumentActionEntite
	 */
	public function getDocumentActionEntite(){
		return $this->objectInstancier->DocumentActionEntite;
	}

	/**
	 * @return DocumentTypeFactory
	 */
	public function getDocumentTypeFactory(){
		return $this->objectInstancier->{'DocumentTypeFactory'};
	}

	/**
	 * @deprecated
	 * @return Entite
	 */
	public function getEntite(){
		static $entite;
		if (empty($entite[$this->id_e])){
			$entite[$this->id_e] = new Entite($this->getSQLQuery(),$this->id_e);
		}
		return $entite[$this->id_e];
	}

	/**
	 * @return EntiteSQL
	 */
	public function getEntiteSQL(){
		return $this->objectInstancier->getInstance("EntiteSQL");
	}

    /**
     * @return SQLQuery
     */
	public function getSQLQuery(){
		return $this->objectInstancier->SQLQuery;
	}
	
	/**
	 * @return NotificationMail
	 */
	public function getNotificationMail(){
		return $this->objectInstancier->NotificationMail;
	}

	/**
	 * @return DocumentType
	 */
	public function getDocumentType(){
		return $this->getDocumentTypeFactory()->getFluxDocumentType($this->type);
	}

	public function getActionName(){
		return $this->getDocumentType()->getAction()->getActionName($this->action);
	}

	/**
	 * Récupération de connecteur
	 * @param $type_connecteur
	 * @return array|bool|mixed
	 * @throws Exception
	 */
	public function getConnecteurId($type_connecteur){
		$num_same_connecteur = $this->getDocumentType()
			->getAction()
			->getProperties($this->action,'num-same-connecteur')?:0;

		$id_ce = $this->getConnecteurFactory()->getConnecteurId($this->id_e,$this->type,$type_connecteur,$num_same_connecteur);
		if (!$id_ce){
			throw new Exception("Aucun connecteur de type $type_connecteur n'est associé au flux {$this->type}");
		}
		return $id_ce;
	}
	
	/**
	 *
	 * @param string $type_connecteur
	 * @throws Exception
	 * @return Connecteur
	 */
	public function getConnecteur($type_connecteur){
		$num_same_connecteur = $this->getDocumentType()
			->getAction()
			->getProperties($this->action,'num-same-connecteur')?:0;

		if (isset($this->connecteurs[$type_connecteur][$num_same_connecteur])){
			return $this->connecteurs[$type_connecteur][$num_same_connecteur] ;
		}
		$id_ce = $this->getConnecteurId($type_connecteur);
		$connecteur = $this->getConnecteurFactory()->getConnecteurById($id_ce);
		$connecteur->setDocDonneesFormulaire($this->getDonneesFormulaire());
		$this->connecteurs[$type_connecteur][$num_same_connecteur] = $connecteur;
		return $connecteur;
	}
	
	/**
	 *
	 * @param string $type_connecteur
	 * @throws Exception
	 * @return DonneesFormulaire
	 */
	public function getConnecteurConfigByType($type_connecteur){
		$num_same_connecteur = $this->getDocumentType()
			->getAction()
			->getProperties($this->action,'num-same-connecteur')?:0;

		if(isset($this->connecteurConfigs[$type_connecteur][$num_same_connecteur])){
			return $this->connecteurConfigs[$type_connecteur][$num_same_connecteur];
		}
		$id_ce = $this->getConnecteurId($type_connecteur);
		$connecteurConfig = $this->getConnecteurConfig($id_ce);
		$this->connecteurConfigs[$type_connecteur][$num_same_connecteur] = $connecteurConfig;
		return $connecteurConfig;
	}
	
	
	/**
	 * @return DonneesFormulaire
	 * @param int $id_ce
	 */
	public function getConnecteurConfig($id_ce){
		return $this->getConnecteurFactory()->getConnecteurConfig($id_ce);
	}
	
	/**
	 * @return DonneesFormulaire
	 */
	public function getConnecteurProperties(){
		return $this->getConnecteurConfig($this->id_ce);
	}
	
	/**
	 * 
	 * @throws Exception
	 * @return Connecteur
	 */
	public function getMyConnecteur(){
		if (! $this->id_ce){
			throw new Exception("Cette action n'est pas une action de connecteur.");
		}
		return $this->getConnecteurFactory()->getConnecteurById($this->id_ce);
	}
	
	/**
	 * @return ConnecteurFactory
	 */
	public function getConnecteurFactory(){
		return $this->objectInstancier->ConnecteurFactory;
	}
	
	public function getGlobalConnecteur($type){
		return $this->getConnecteurFactory()->getGlobalConnecteur($type);
	}
	
	
	/***** Fonction utilitaire *****/
	
	public function addActionOK($message = ""){
		$this->changeAction($this->action, $message);
	}
	
	public function changeAction($action,$message){
		$this->objectInstancier->ActionChange->addAction($this->id_d,$this->id_e,$this->id_u,$action,$message);
		$this->setLastMessage($message);
	}
	
	public function notify($actionName,$type,$message){
		$this->getNotificationMail()->notify($this->id_e,$this->id_d,$actionName,$type,$message);
	}

	public function redirect($to){
		if (! $this->from_api) {
		    $location = SITE_BASE.ltrim($to,"/");
			header("Location: $location");
			exit;
		}
	}

	/**
	 * @param $object
	 * @param $intf
	 * @return bool
	 * @throws Exception
	 */
	public function checkIntf($object, $intf) {
		if (! ($object instanceof $intf)) {
			throw new Exception('L\'objet ' . get_class($object) . ' n\'implémente pas le contrat d\'interface ' . $intf);
		}
		return true;
	}

	/**
	 * Méthode standard pour le traitement par lot : on enregistre dans la job queue les travaux qui s'éxecuteront de manière asynchrone
	 * @param array $all_id_d
	 */
	public function goLot(array $all_id_d){
		foreach($all_id_d as $id_d){
			$this->objectInstancier->JobManager->setTraitementLot($this->id_e,$id_d,$this->id_u,$this->action);
			$this->objectInstancier->Journal->add(Journal::DOCUMENT_TRAITEMENT_LOT,$this->id_e,$id_d,$this->action,"Programmation dans le cadre d'un traitement par lot");
		}
	}

	//Lors d'un traitement par lot spécifique (synchrone par exemple), il est nécessaire de réactiver le job manager pour le docuemnt en question
	public function setJobManagerForLot(array $all_id_d){
		/** @var JobManager $jobManager */
		$jobManager = $this->objectInstancier->getInstance("JobManager");

		foreach($all_id_d as $id_d){
			$jobManager->setJobForDocument($this->id_e, $id_d,"suite traitement par lot");
		}
	}

	/** @var  InternalAPI */
	private $internalAPI;

    public function apiCall($method,$ressource,$data){
        if (! $this->internalAPI) {
            $this->internalAPI = $this->objectInstancier->getInstance("InternalAPI");
            $this->internalAPI->setCallerType(InternalAPI::CALLER_TYPE_CONSOLE);
            $this->internalAPI->setFileUploader($this->objectInstancier->getInstance("FileUploader"));
            $this->internalAPI->setUtilisateurId($this->id_u);
        }
        return $this->internalAPI->$method($ressource,$data);
    }

    protected function apiGet($ressource,$data){
        return $this->apiCall('get',$ressource,$data);
    }

	private $logger;
	public function setLogger(Monolog\Logger $logger){
		$this->logger = $logger;
	}

	/**
	 * @return Monolog\Logger
	 */
	public function getLogger(){
		return $this->logger;
	}

	public function getIdMapping() : StringMapper {
		$connecteur_type_mapping = $this->getDocumentType()->getAction()->getProperties(
			$this->action,
			ACTION::CONNECTEUR_TYPE_MAPPING
		)?:[];
		$stringMapper = new StringMapper();
		$stringMapper->setMapping($connecteur_type_mapping);
		return $stringMapper;
	}

	abstract public function go();
	
}