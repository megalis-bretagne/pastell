<?php

/**
 * @var Logger $logger
 */

use Monolog\Logger;
use Pastell\Database\DatabaseUpdater;
use Pastell\Service\FeatureToggleService;
use Pastell\Utilities\Identifier\IdentifierGeneratorInterface;
use Pastell\Utilities\Identifier\UuidGenerator;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\Lock\Store\RedisStore;

require_once __DIR__ . '/init-no-db.php';

$objectInstancier = new ObjectInstancier();
ObjectInstancierFactory::setObjectInstancier($objectInstancier);

$objectInstancier->setInstance(Logger::class, $logger);
$objectInstancier->setInstance('log_level', LOG_LEVEL);
$objectInstancier->setInstance('pastell_path', PASTELL_PATH);
$objectInstancier->setInstance('data_dir', __DIR__ . '/data');

$objectInstancier->setInstance(PastellTimer::class, new PastellTimer());
$objectInstancier->setInstance('site_base', \rtrim(SITE_BASE, '/'));
$objectInstancier->setInstance('websec_base', WEBSEC_BASE);
$objectInstancier->setInstance('list_pack', LIST_PACK);

$objectInstancier->setInstance('temp_directory', sys_get_temp_dir());

$objectInstancier->setInstance('workspacePath', WORKSPACE_PATH);
$objectInstancier->setInstance('template_path', TEMPLATE_PATH);

$objectInstancier->setInstance('opensslPath', OPENSSL_PATH);

$objectInstancier->setInstance('bd_dsn', BD_DSN);
$objectInstancier->setInstance('bd_user', BD_USER);
$objectInstancier->setInstance('bd_password', BD_PASS);

$objectInstancier->setInstance('redis_server', REDIS_SERVER);
$objectInstancier->setInstance('redis_port', REDIS_PORT);

if (REDIS_SERVER && !TESTING_ENVIRONNEMENT) {
    $objectInstancier->setInstance(RedisWrapper::class, new RedisWrapper(REDIS_SERVER, REDIS_PORT));
    $objectInstancier->setInstance(MemoryCache::class, $objectInstancier->getInstance(RedisWrapper::class));
    $redis = new Redis();
    $redis->connect(REDIS_SERVER, REDIS_PORT);
    $redisStore = new RedisStore($redis);
    $objectInstancier->setInstance(LockFactory::class, new LockFactory($redisStore));
} else {
    $objectInstancier->setInstance(MemoryCache::class, new StaticWrapper());
    $objectInstancier->setInstance(LockFactory::class, new LockFactory(new InMemoryStore()));
}

//if (TESTING_ENVIRONNEMENT) {
//    $objectInstancier->setInstance(ProofBackend::class, new ProofBackendTest());
//} else {
$objectInstancier->setInstance('S3url', S3_ENDPOINT);
$objectInstancier->setInstance('S3key', S3_KEY);
$objectInstancier->setInstance('S3secret', S3_SECRET);
$objectInstancier->setInstance('S3bucket', S3_BUCKET);
$objectInstancier->setInstance(ProofBackend::class, new S3Wrapper(S3_ENDPOINT, S3_KEY, S3_SECRET, S3_BUCKET));
//}

$objectInstancier->setInstance('cache_ttl_in_seconds', CACHE_TTL_IN_SECONDS);
$objectInstancier->setInstance('disable_job_queue', DISABLE_JOB_QUEUE);
$objectInstancier->setInstance('disable_journal_horodatage', DISABLE_JOURNAL_HORODATAGE);

$objectInstancier->setInstance(IdentifierGeneratorInterface::class, new PasswordGenerator());
if (USE_UUID_FOR_DOCUMENT) {
    $objectInstancier->setInstance(IdentifierGeneratorInterface::class, new UuidGenerator());
}

$id_u_journal = 0;
if ($objectInstancier->getInstance(Authentification::class)->isConnected()) {
    $id_u_journal = $objectInstancier->getInstance(Authentification::class)->getId();
}
$objectInstancier->getInstance(Journal::class)->setId($id_u_journal);

try {
    $horodateur = $objectInstancier->getInstance(ConnecteurFactory::class)->getGlobalConnecteur('horodateur');
    if ($horodateur) {
        $objectInstancier->getInstance(Journal::class)->setHorodateur($horodateur);
    }
} catch (Exception $e) {
    /** Nothing to do */
}


/** @var SQLQuery $sqlQuery */
$sqlQuery = $objectInstancier->getInstance(SQLQuery::class);

$sqlQuery->setLogger($logger);

$authentification = $objectInstancier->getInstance(Authentification::class);


$journal = $objectInstancier->getInstance(Journal::class);
$documentTypeFactory = $objectInstancier->getInstance(DocumentTypeFactory::class);
$donneesFormulaireFactory = $objectInstancier->getInstance(DonneesFormulaireFactory::class);
$roleUtilisateur = $objectInstancier->getInstance(RoleUtilisateur::class);

if (PHP_SAPI !== 'cli' || $objectInstancier->getInstance(SQLQuery::class)->isConnected()) {
    $objectInstancier->getInstance(Extensions::class)->autoloadExtensions();
}


$objectInstancier->setInstance('journal_max_age_in_months', JOURNAL_MAX_AGE_IN_MONTHS);
$objectInstancier->setInstance('database_file', DatabaseUpdater::DATABASE_FILE);
$objectInstancier->setInstance('rgpd_page_path', RGPD_PAGE_PATH);

$htmlPurifier = new HTMLPurifier();
$htmlPurifier->config->set('Cache.SerializerPath', HTML_PURIFIER_CACHE_PATH);
$objectInstancier->setInstance(HTMLPurifier::class, $htmlPurifier);

$objectInstancier->setInstance('http_proxy_url', HTTP_PROXY_URL);
$objectInstancier->setInstance('no_proxy', NO_PROXY);
$objectInstancier->setInstance('pes_viewer_url', PES_VIEWER_URL);
$objectInstancier->setInstance('password_min_entropy', PASSWORD_MIN_ENTROPY);
$objectInstancier->setInstance('mailer_dsn', MAILER_DSN);
$objectInstancier->setInstance('email_template_path', __DIR__ . '/templates/email/');
$objectInstancier->setInstance('plateforme_mail', PLATEFORME_MAIL);

$objectInstancier->setInstance('admin_email', \explode(',', ADMIN_EMAIL));

$objectInstancier->setInstance('pastell_admin_login', PASTELL_ADMIN_LOGIN);
$objectInstancier->setInstance('pastell_admin_email', PASTELL_ADMIN_EMAIL);

$objectInstancier->setInstance('unlock_job_error_at_startup', UNLOK_JOB_ERROR_AT_STARTUP);

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
