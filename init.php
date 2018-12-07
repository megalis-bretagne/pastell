<?php
require_once(__DIR__."/init-no-db.php");

$objectInstancier = new ObjectInstancier();

$objectInstancier->setInstance("Monolog\Logger",$logger);
$objectInstancier->pastell_path = PASTELL_PATH;
$objectInstancier->PastellTimer = new PastellTimer();

$objectInstancier->temp_directory = sys_get_temp_dir();


$objectInstancier->workspacePath = WORKSPACE_PATH;
$objectInstancier->template_path = TEMPLATE_PATH;

$objectInstancier->opensslPath = OPENSSL_PATH;

$objectInstancier->bd_dsn = BD_DSN;
$objectInstancier->bd_user = BD_USER;
$objectInstancier->bd_password = BD_PASS;

$objectInstancier->daemon_log_file = DAEMON_LOG_FILE;


$objectInstancier->upstart_touch_file = UPSTART_TOUCH_FILE;
$objectInstancier->upstart_time_send_warning = UPSTART_TIME_SEND_WARNING;

$objectInstancier->open_id_url_callback = SITE_BASE."/Connexion/openIdReturn";

if (REDIS_SERVER && ! TESTING_ENVIRONNEMENT) {
    $objectInstancier->MemoryCache = new RedisWrapper(REDIS_SERVER, REDIS_PORT);
} else {
    $objectInstancier->MemoryCache = new StaticWrapper();
}

$objectInstancier->cache_ttl_in_seconds = CACHE_TTL_IN_SECONDS;

$objectInstancier->disable_job_queue = DISABLE_JOB_QUEUE;


$id_u_journal = 0;
if ($objectInstancier->Authentification->isConnected()) {
	$id_u_journal = $objectInstancier->Authentification->getId();
}
$objectInstancier->Journal->setId($id_u_journal);

try {
	$horodateur = $objectInstancier->ConnecteurFactory->getGlobalConnecteur('horodateur');
	if ($horodateur){
		$objectInstancier->Journal->setHorodateur($horodateur);
	}
} catch (Exception $e){}


/** @var SQLQuery $sqlQuery */
$sqlQuery = $objectInstancier->SQLQuery;

$sqlQuery->setLogger($logger);

$authentification = $objectInstancier->Authentification;
$journal = $objectInstancier->Journal;
$documentTypeFactory = $objectInstancier->DocumentTypeFactory;
$donneesFormulaireFactory = $objectInstancier->DonneesFormulaireFactory;
$roleUtilisateur = $objectInstancier->RoleUtilisateur;

define("DATABASE_FILE", PASTELL_PATH."/installation/pastell.bin");


$objectInstancier->Extensions->loadConnecteurType();

$daemon_command = PHP_PATH." ".realpath(__DIR__."/batch/pastell-job-master.php");

$objectInstancier->DaemonManager = new DaemonManager($daemon_command,PID_FILE,DAEMON_LOG_FILE, DAEMON_USER);


$objectInstancier->daemon_user = DAEMON_USER;
$objectInstancier->setInstance('journal_max_age_in_months', JOURNAL_MAX_AGE_IN_MONTHS);
$objectInstancier->setInstance('admin_email', ADMIN_EMAIL);
$objectInstancier->setInstance('database_file',__DIR__."/installation/pastell.bin");