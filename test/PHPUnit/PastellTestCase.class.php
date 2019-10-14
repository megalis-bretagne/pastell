<?php

define("FIXTURES_PATH",__DIR__."/fixtures/");



abstract class PastellTestCase extends LegacyPHPUnit_Framework_TestCase {

	const ID_E_COL = 1;
	const ID_E_SERVICE = 2;
	const ID_U_ADMIN = 1;

	private $databaseConnection;
	private $objectInstancier;

	private $emulated_disk;

	public static function getSQLQuery(){
		static $sqlQuery;
		if (! $sqlQuery) {
			$sqlQuery = new SQLQuery(BD_DSN_TEST,BD_USER_TEST,BD_PASS_TEST);
		}
		return $sqlQuery;
	}

	public function __construct($name = NULL, array $data = array(), $dataName = ''){
		parent::__construct($name,$data,$dataName);
		$this->objectInstancier = new ObjectInstancier();
		ObjectInstancierFactory::setObjectInstancier($this->objectInstancier);

		$this->objectInstancier->{'daemon_command'} = "/bin/date";
		$this->objectInstancier->{'pid_file'} = "/tmp/test";
		$this->objectInstancier->{'log_file'} = "/tmp/test";

		$daemonManager = new DaemonManager("/bin/date", "/tmp/test", "/tmp/test", 0);
		$this->objectInstancier->{'DaemonManager'} = $daemonManager;

		$this->objectInstancier->{'pastell_path'} = PASTELL_PATH;
		$this->objectInstancier->{'SQLQuery'} = self::getSQLQuery();
		$this->objectInstancier->{'template_path'} = TEMPLATE_PATH;

		$this->objectInstancier->{'MemoryCache'} = new StaticWrapper();
        $this->getObjectInstancier()->setInstance(
            RoleUtilisateur::class,
            new RoleUtilisateur(
                $this->getSQLQuery(),
                $this->getObjectInstancier()->getInstance(RoleSQL::class),
                new MemoryCacheNone(),
                0
            ));

		$this->objectInstancier->{'ManifestFactory'} = new ManifestFactory(__DIR__."/fixtures/",new YMLLoader(new MemoryCacheNone()));

		$this->objectInstancier->{'temp_directory'} = sys_get_temp_dir();
		$this->objectInstancier->{'upstart_touch_file'} = sys_get_temp_dir()."/upstart.mtime";
		$this->objectInstancier->{'upstart_time_send_warning'} = 600;
		$this->objectInstancier->{'disable_job_queue'} = false;
		$this->objectInstancier->{'cache_ttl_in_seconds'} = 10;
        $this->objectInstancier->setInstance('rgpd_page_path',RGPD_PAGE_PATH);

		$this->objectInstancier->setInstance("Monolog\Logger",new  Monolog\Logger('PHPUNIT'));
		$this->objectInstancier->setInstance('log_level',\Monolog\Logger::DEBUG);
		$testHandler = new Monolog\Handler\TestHandler();
		$this->objectInstancier->setInstance("Monolog\Handler\TestHandler",$testHandler);
		$this->getObjectInstancier()->getInstance("Monolog\Logger")->pushHandler($testHandler);
		$this->reinitFileSystem();
		$this->getJournal()->setId(1);

		$this->objectInstancier->{'opensslPath'} = OPENSSL_PATH;

		$daemon_command = PHP_PATH." ".realpath(__DIR__."/batch/pastell-job-master.php");

		$this->objectInstancier->{'DaemonManager'} = new DaemonManager($daemon_command,PID_FILE,DAEMON_LOG_FILE, DAEMON_USER);
		$this->objectInstancier->setInstance('daemon_user','www-data');
		$this->objectInstancier->setInstance('journal_max_age_in_months',2);
		$this->objectInstancier->setInstance('admin_email',"mettre_un_email");
		$this->objectInstancier->setInstance('database_file',__DIR__."/../../installation/pastell.bin");
    }

	public function getObjectInstancier(){
		return $this->objectInstancier;
	}

	public function reinitFileSystem(){

	    $structure = array(
				'workspace' => array(
					'connecteur_1.yml' => '---
iparapheur_type: Actes
iparapheur_retour: Archive',

		),
				'log' => array(),
				'tmp' => array()
		);
		org\bovigo\vfs\vfsStream::setup('test',null,$structure);
		$this->emulated_disk = org\bovigo\vfs\vfsStream::url('test');
		$this->objectInstancier->{'workspacePath'} = $this->emulated_disk."/workspace/";
	}


	public function getEmulatedDisk(){
		return $this->emulated_disk;
	}

	public function reinitDatabase(){
        $this->getSQLQuery()->query(file_get_contents(__DIR__."/pastell_test.sql"));
	}

	/**
	 * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	public function getConnection() {
		return $this->databaseConnection;
	}

	/**
	 * @deprecated 3.0 use ExtensionsLoader class instead
	 * @param array $extension_path_list
	 * @return array
	 */
	protected function loadExtension(array $extension_path_list){
		$extensionLoader = $this->getObjectInstancier()->getInstance(ExtensionLoader::class);
		return $extensionLoader->loadExtension($extension_path_list);
	}

	protected function setUp(){
		parent::setUp();
		$this->reinitDatabase();
		$this->reinitFileSystem();
		$_POST = array();
		$_GET = array();
	}

    /**
	 * @return Journal
	 */
	protected function getJournal(){
		return $this->objectInstancier->getInstance("Journal");
	}

	/**
	 * @return ConnecteurFactory
	 */
	protected function getConnecteurFactory(){
		return $this->getObjectInstancier()->getInstance('ConnecteurFactory');
	}

	/**
	 * @return DonneesFormulaireFactory
	 */
	protected function getDonneesFormulaireFactory(){
		return $this->getObjectInstancier()->{'DonneesFormulaireFactory'};
	}

	protected function getAPIController($controllerName,$id_u){
		/** @var  BaseAPIControllerFactory $factory */
		$factory = $this->getObjectInstancier()->getInstance('BaseAPIControllerFactory');

		if ($id_u) {
			//FIXME : Faudrait pas que ca arrive...
			/** @var Authentification $authentification */
			$authentification = $this->objectInstancier->getInstance('Authentification');
			$authentification->connexion('API', $id_u);
		}
		return $factory->getInstance($controllerName,$id_u);
	}

	/** @var  InternalAPI */
	private $internalAPI;

	protected function getInternalAPI(){
	    return $this->getInternalAPIAsUser(1);
	}

    protected function getInternalAPIAsUser($userId): InternalAPI
    {
        $this->internalAPI = $this->getObjectInstancier()->getInstance(InternalAPI::class);
        $this->internalAPI->setUtilisateurId($userId);
        return $this->internalAPI;
    }

	protected function getV1($ressource){
		$apiAuthetication = $this->getMockBuilder('ApiAuthentication')->disableOriginalConstructor()->getMock();
		$apiAuthetication->expects($this->any())->method("getUtilisateurId")->willReturn(1);
		$this->getObjectInstancier()->setInstance('ApiAuthentication',$apiAuthetication);

		/** @var HTTP_API $httpAPI */
		$httpAPI = $this->getObjectInstancier()->getInstance("HTTP_API");

		$path = parse_url($ressource,PHP_URL_PATH);
		$query = parse_url($ressource,PHP_URL_QUERY);
		parse_str($query,$data_from_query);

		$httpAPI->setServerArray(array('REQUEST_METHOD'=>'get'));
		$data_from_query[HTTP_API::PARAM_API_FUNCTION] = $path;
		$httpAPI->setGetArray($data_from_query);
		$httpAPI->setRequestArray($data_from_query);
		$httpAPI->dispatch();
	}

	/**
	 * @return Monolog\Logger
	 */
	public function getLogger(){
		return $this->getObjectInstancier()->getInstance("Monolog\Logger");
	}

	public function getLogRecords(){
		$testHandler = $this->getObjectInstancier()->getInstance("Monolog\Handler\TestHandler");
		return $testHandler->getRecords();
	}

    /**
     * Creates and returns a document of the type in parameter
     *
     * @param string $type
     * @param int $entite
     * @return array The document
     */
    protected function createDocument($type, $entite = self::ID_E_COL) {
        return $this->getInternalAPI()->post("/Document/$entite", [
                'type' => $type
            ]
        );
    }

    /**
     * @param $id_document
     * @param array $data
     * @param int $entite
     * @return mixed
     */
    protected function configureDocument($id_document, array $data, $entite = self::ID_E_COL) {
        return $this->getInternalAPI()->patch("/entite/$entite/document/$id_document/content/", $data);
    }

    /**
     * Creates and returns a connector
     *
     * @param string $id_connecteur
     * @param string $libelle
     * @param int $entite
     * @return array The connector
     */
    protected function createConnector($id_connecteur, $libelle, $entite = self::ID_E_COL) : array {
        return $this->getInternalAPI()->post("/entite/$entite/connecteur/", [
                'id_connecteur' => $id_connecteur,
                'libelle' => $libelle,
            ]
        );
    }

    protected function createConnecteurForTypeDossier($flux,$id_connecteur,$id_e = self::ID_E_COL){
    	$connecteur_info = $this->createConnector($id_connecteur,"Connectuer $id_connecteur",$id_e);
    	$id_ce = $connecteur_info['id_ce'];
    	$type = $this->getObjectInstancier()
			->getInstance(ConnecteurDefinitionFiles::class)
			->getInfo($id_connecteur)['type'];
    	$this->associateFluxWithConnector($id_ce,$flux,$type);
		return $id_ce;
	}


    /**
     * Configures the content of a connector
     *
     * @param int $id_ce The id of the connector
     * @param array $data
     * @param int $entite
     * @return mixed
     */
    protected function configureConnector($id_ce, array $data, $entite = self::ID_E_COL) {
        return $this->getInternalAPI()->patch("/entite/$entite/connecteur/$id_ce/content/", $data);
    }

    /**
     * Associates a flux with a connector
     *
     * @param int $id_ce The id of the connector
     * @param string $flux
     * @param string $type
     * @param int $entite
     * @param int $num_same_type
     * @return mixed
     */
    protected function associateFluxWithConnector($id_ce, $flux, $type, $entite = self::ID_E_COL, $num_same_type = 0) {
        return $this->getInternalAPI()->post("/entite/$entite/flux/$flux/connecteur/$id_ce", [
                'type' => $type,
				'num_same_type'=>$num_same_type
            ]
        );
    }

    /**
     * Triggers an action on a document and returns the success or not of this action
     *
     * @param string $id_d The id of the document
     * @param string $action
     * @param int $entite
     * @param int $user
     * @return bool
     */
    protected function triggerActionOnDocument($id_d, $action, $entite = self::ID_E_COL, $user = self::ID_U_ADMIN) {
        return $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class)->executeOnDocument(
            $entite,
            $user,
            $id_d,
            $action
        );
    }

    /**
     * Triggers an action on a connector and returns the success or not of this action
     *
     * @param int $id_ce The id of the connector
     * @param string $action
     * @param int $user
     * @return bool
     */
    protected function triggerActionOnConnector($id_ce, $action, $user = self::ID_U_ADMIN) {
        return $this->getObjectInstancier()->getInstance('ActionExecutorFactory')->executeOnConnecteur(
            $id_ce,
            $user,
            $action
        );
    }

    /**
     * Asserts that this is the last message received
     *
     * @param string $last_message
     */
    protected function assertLastMessage($last_message) {
        $this->assertEquals(
            $last_message,
            $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class)->getLastMessage()
        );
    }

    protected function assertLastDocumentAction($expected_action,$id_d,$id_e=self::ID_E_COL){
		$this->assertEquals(
			$expected_action,
    		$this->getObjectInstancier()->getInstance(DocumentActionEntite::class)->getLastAction($id_e,$id_d)
		);
	}

	/**
	 * @param $id_d
	 * @param array $action_possible
	 * @param int $id_e
	 * @param int $id_u
	 * @throws Exception
	 */
	protected function assertActionPossible(array $expected_action_possible,$id_d,$id_e=self::ID_E_COL,$id_u=0){
		$actionPossible = $this->getObjectInstancier()->getInstance(ActionPossible::class);
		$this->assertSame(
			$expected_action_possible,
			$actionPossible->getActionPossible($id_e,$id_u,$id_d)
		);
	}

}