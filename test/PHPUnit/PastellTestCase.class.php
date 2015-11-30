<?php 
//require_once 'vfsStream/vfsStream.php';

require_once "PHPUnit/Extensions/Database/TestCase.php";

define("FIXTURES_PATH",__DIR__."/fixtures/");

abstract class PastellTestCase extends PHPUnit_Extensions_Database_TestCase {
	
	const ID_E_COL = 1;
	const ID_E_SERVICE = 2;
	const ID_U_ADMIN = 1;
	
	
	private $databaseConnection;
	private $objectInstancier;
		
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
		
		$this->objectInstancier->daemon_command = "/bin/date";
		$this->objectInstancier->pid_file = "/tmp/test";
		$this->objectInstancier->log_file = "/tmp/test";
		
		$daemonManager = new DaemonManager("/bin/date", "/tmp/test", "/tmp/test", 0);
		$this->objectInstancier->DaemonManager = $daemonManager;
		
		$this->objectInstancier->pastell_path = PASTELL_PATH;
		$this->objectInstancier->SQLQuery = self::getSQLQuery();
		$this->objectInstancier->template_path = TEMPLATE_PATH;

		$this->objectInstancier->MemoryCache = new MemoryCacheNone();
		$this->objectInstancier->ManifestFactory = new ManifestFactory(__DIR__."/fixtures/",new YMLLoader(new MemoryCacheNone()));
		
		$this->objectInstancier->temp_directory = sys_get_temp_dir();
		$this->objectInstancier->upstart_touch_file = sys_get_temp_dir()."/upstart.mtime";
		$this->objectInstancier->upstart_time_send_warning = 600;
		$this->objectInstancier->Journal->setId(1);

		$this->objectInstancier->opensslPath = OPENSSL_PATH;

		$this->objectInstancier->api_definition_file_path = PASTELL_PATH . "/pastell-core/api-definition.yml";

		$daemon_command = PHP_PATH." ".realpath(__DIR__."/batch/pastell-job-master.php");
		
		$this->objectInstancier->DaemonManager = new DaemonManager($daemon_command,PID_FILE,DAEMON_LOG_FILE, DAEMON_USER);
		$this->databaseConnection = $this->createDefaultDBConnection($this->objectInstancier->SQLQuery->getPdo(), BD_DBNAME_TEST);
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
				'log' => array()
		);		
		org\bovigo\vfs\vfsStream::setup('test',null,$structure);
		$testStreamUrl = org\bovigo\vfs\vfsStream::url('test');
		$this->objectInstancier->workspacePath = $testStreamUrl."/workspace/";
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
	
	
}