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
		
		$this->objectInstancier->{'daemon_command'} = "/bin/date";
		$this->objectInstancier->{'pid_file'} = "/tmp/test";
		$this->objectInstancier->{'log_file'} = "/tmp/test";
		
		$daemonManager = new DaemonManager("/bin/date", "/tmp/test", "/tmp/test", 0);
		$this->objectInstancier->{'DaemonManager'} = $daemonManager;
		
		$this->objectInstancier->{'pastell_path'} = PASTELL_PATH;
		$this->objectInstancier->{'SQLQuery'} = self::getSQLQuery();
		$this->objectInstancier->{'template_path'} = TEMPLATE_PATH;

		$this->objectInstancier->{'MemoryCache'} = new StaticWrapper();

		$this->objectInstancier->{'ManifestFactory'} = new ManifestFactory(__DIR__."/fixtures/",new YMLLoader(new MemoryCacheNone()));
		
		$this->objectInstancier->{'temp_directory'} = sys_get_temp_dir();
		$this->objectInstancier->{'upstart_touch_file'} = sys_get_temp_dir()."/upstart.mtime";
		$this->objectInstancier->{'upstart_time_send_warning'} = 600;
		$this->objectInstancier->{'disable_job_queue'} = false;
		$this->getJournal()->setId(1);

		$this->objectInstancier->{'opensslPath'} = OPENSSL_PATH;

		$daemon_command = PHP_PATH." ".realpath(__DIR__."/batch/pastell-job-master.php");
		
		$this->objectInstancier->{'DaemonManager'} = new DaemonManager($daemon_command,PID_FILE,DAEMON_LOG_FILE, DAEMON_USER);


        $this->objectInstancier->Logger = new  Monolog\Logger('PASTELL');
        $this->objectInstancier->Logger->pushHandler(new Monolog\Handler\NullHandler());
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

	protected function loadExtension(array $extension_path_list){
		$result= array();
		/** @var ExtensionSQL $extensionSQL */
		$extensionSQL = $this->getObjectInstancier()->getInstance('ExtensionSQL');
		foreach($extension_path_list as $ext){
			$extensionSQL->edit(0,$ext);
			$result[$ext] = $extensionSQL->getLastInsertId();
		}
		/** @var Extensions $extensions */
		$extensions = $this->getObjectInstancier()->getInstance('Extensions');
		$extensions->loadConnecteurType();
		return $result;
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
		$this->internalAPI = $this->getObjectInstancier()->getInstance('InternalAPI');
		$this->internalAPI->setUtilisateurId(1);
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

}