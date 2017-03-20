<?php 

define("FIXTURES_PATH",__DIR__."/fixtures/");

abstract class PastellTestCase extends PHPUnit_Extensions_Database_TestCase {
	
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

		/** Il est nécessaire de mettre apc.enable_cli à 1 dans le php.ini  */
		$this->objectInstancier->{'MemoryCache'} = new APCWrapper();

		$this->objectInstancier->{'ManifestFactory'} = new ManifestFactory(__DIR__."/fixtures/",new YMLLoader(new MemoryCacheNone()));
		
		$this->objectInstancier->{'temp_directory'} = sys_get_temp_dir();
		$this->objectInstancier->{'upstart_touch_file'} = sys_get_temp_dir()."/upstart.mtime";
		$this->objectInstancier->{'upstart_time_send_warning'} = 600;
		$this->getJournal()->setId(1);

		$this->objectInstancier->{'opensslPath'} = OPENSSL_PATH;

		$daemon_command = PHP_PATH." ".realpath(__DIR__."/batch/pastell-job-master.php");
		
		$this->objectInstancier->{'DaemonManager'} = new DaemonManager($daemon_command,PID_FILE,DAEMON_LOG_FILE, DAEMON_USER);
		$this->databaseConnection = $this->createDefaultDBConnection(self::getSQLQuery()->getPdo(), BD_DBNAME_TEST);
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
		$this->getConnection()->createDataSet();
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

	/**
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet() {
		return new PHPUnit_Extensions_Database_DataSet_YamlDataSet( __DIR__."/database_data.yml");
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



	protected function launchWorkerInSession($id_e,$id_d){
		/** @var JobQueueSQL $jobQueueSQL */
		$jobQueueSQL = $this->getObjectInstancier()->{'JobQueueSQL'};
		$id_job = $jobQueueSQL->getJobIdForDocument($id_e,$id_d);

		/** @var JobManager $jobManager */
		$jobManager = $this->getObjectInstancier()->{'JobManager'};
		$jobManager->launchJob($id_job);
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
}