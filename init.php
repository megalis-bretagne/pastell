<?php

use Pastell\Service\FeatureToggleService;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\Lock\Store\RedisStore;

require_once(__DIR__ . "/init-no-db.php");

$objectInstancier = new ObjectInstancier();
ObjectInstancierFactory::setObjectInstancier($objectInstancier);


$objectInstancier->setInstance("Monolog\Logger", $logger);
$objectInstancier->setInstance('log_level', LOG_LEVEL);
$objectInstancier->pastell_path = PASTELL_PATH;
$objectInstancier->PastellTimer = new PastellTimer();
$objectInstancier->setInstance('site_base', SITE_BASE);
$objectInstancier->setInstance('list_pack', LIST_PACK);

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

$objectInstancier->open_id_url_callback = SITE_BASE . "/Connexion/openIdReturn";

if (REDIS_SERVER && !TESTING_ENVIRONNEMENT) {
    $objectInstancier->RedisWrapper = new RedisWrapper(REDIS_SERVER, REDIS_PORT);
    $objectInstancier->MemoryCache = $objectInstancier->RedisWrapper;
    $redis = new Redis();
    $redis->connect(REDIS_SERVER, REDIS_PORT);
    $redisStore = new RedisStore($redis);
    $objectInstancier->setInstance(LockFactory::class, new LockFactory($redisStore));
} else {
    $objectInstancier->MemoryCache = new StaticWrapper();
    $objectInstancier->setInstance(LockFactory::class, new LockFactory(new InMemoryStore()));
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
    if ($horodateur) {
        $objectInstancier->Journal->setHorodateur($horodateur);
    }
} catch (Exception $e) {
    /** Nothing to do */
}


/** @var SQLQuery $sqlQuery */
$sqlQuery = $objectInstancier->SQLQuery;

$sqlQuery->setLogger($logger);

$authentification = $objectInstancier->Authentification;
$journal = $objectInstancier->Journal;
$documentTypeFactory = $objectInstancier->DocumentTypeFactory;
$donneesFormulaireFactory = $objectInstancier->DonneesFormulaireFactory;
$roleUtilisateur = $objectInstancier->RoleUtilisateur;

define("DATABASE_FILE", PASTELL_PATH . "/installation/pastell.bin");


$objectInstancier->Extensions->loadConnecteurType();

$daemon_command = PHP_PATH . " " . realpath(__DIR__ . "/batch/pastell-job-master.php");

$objectInstancier->DaemonManager = new DaemonManager($daemon_command, PID_FILE, DAEMON_LOG_FILE, DAEMON_USER);


$objectInstancier->daemon_user = DAEMON_USER;
$objectInstancier->setInstance('journal_max_age_in_months', JOURNAL_MAX_AGE_IN_MONTHS);
$objectInstancier->setInstance('admin_email', ADMIN_EMAIL);
$objectInstancier->setInstance('database_file', __DIR__ . "/installation/pastell.bin");
$objectInstancier->setInstance('rgpd_page_path', RGPD_PAGE_PATH);

$htmlPurifier = new HTMLPurifier();
$htmlPurifier->config->set('Cache.SerializerPath', HTML_PURIFIER_CACHE_PATH);
$objectInstancier->setInstance(HTMLPurifier::class, $htmlPurifier);

$objectInstancier->setInstance('connecteur_droit', CONNECTEUR_DROIT);
$objectInstancier->setInstance('http_proxy_url', HTTP_PROXY_URL);
$objectInstancier->setInstance('no_proxy', NO_PROXY);
$objectInstancier->setInstance('pes_viewer_url', PES_VIEWER_URL);
$objectInstancier->setInstance('password_min_entropy', PASSWORD_MIN_ENTROPY);



$featureToggleService = $objectInstancier->getInstance(FeatureToggleService::class);

/**
 * @var $feature_toggle array
 * @var  $classname string
 * @var  $enable bool
 */
foreach ($feature_toggle ?? [] as $classname => $enable) {
    if ($enable) {
        $featureToggleService->enable($classname);
    } else {
        $featureToggleService->disable($classname);
    }
}
