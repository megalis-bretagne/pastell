<?php
 
//Ce fichier contient les valeurs par défaut

if (file_exists(__DIR__ . "/LocalSettings.php")) {
    //Il est possible d'écraser les valeurs par défaut en
    //créant un fichier LocalSettings.php
    require_once(__DIR__ . "/LocalSettings.php");
}

foreach (glob("/data/config/*.php") as $file_name) {
    include_once($file_name);
}


if (! defined("PASTELL_PATH")) {
    define("PASTELL_PATH", __DIR__ . "/");
}

if (! defined("ADMIN_EMAIL")) {
    define("ADMIN_EMAIL", "mettre_un_email");
}

//Emplacement du répertoire pour sauvegarder les fichiers temporaires
//ATTENTION : CE RÉPERTOIRE DOIT ÊTRE ACCESSIBLE EN ECRITURE
if (!defined("WORKSPACE_PATH")) {
    define("WORKSPACE_PATH", PASTELL_PATH . "/workspace");
}

//Définition de la connexion à la base de données
if (!defined("BD_DSN")) {
    define("BD_DSN", "mysql:dbname=pastell;host=127.0.0.1;port=3306;charset=utf8");
}
if (!defined("BD_USER")) {
    define("BD_USER", "pastell");
}
if (!defined("BD_PASS")) {
    define("BD_PASS", "pastell");
}


//Définition de la connexion à la base de données pour les tests unitaires et les tests de validation
if (!defined("BD_DSN_TEST")) {
    define("BD_DSN_TEST", "mysql:dbname=pastell_test;host=localhost;port=8889");
}
if (!defined("BD_USER_TEST")) {
    define("BD_USER_TEST", "user");
}
if (!defined("BD_PASS_TEST")) {
    define("BD_PASS_TEST", "user");
}
if (!defined("BD_DBNAME_TEST")) {
    define("BD_DBNAME_TEST", "pastell_test");
}



//Attention, il faut une version d'openSSL > 1.0.0a
if (! defined("OPENSSL_PATH")) {
    //Une mauvaise définition du chemin entraîne une erreur fatale lors de la tentative de connexion.
    //OpenSSL est OBLIGATOIRE sur Pastell
    define("OPENSSL_PATH", "/usr/bin/openssl");
}

//Racine du site Pastell
//ex : http://pastell.libriciel.coop/
//ex : http://pastell.libriciel.coop/pastell/
//Toujours finir l'adresse par un /
if (!defined("SITE_BASE")) {
    define("SITE_BASE", "http://localhost/pastell/web/");
}

if (!defined("WEBSEC_BASE")) {
    define("WEBSEC_BASE", "http://localhost/pastell/web-mailsec/");
}

if (!defined("AGENT_FILE_PATH")) {
    define("AGENT_FILE_PATH", "/tmp/agent");
}
if (! defined("PRODUCTION")) {
    define("PRODUCTION", false);
}

if (!defined("PLATEFORME_MAIL")) {
    define("PLATEFORME_MAIL", "pastell@noreply");
}

if (!defined("UPSTART_TOUCH_FILE")) {
    define("UPSTART_TOUCH_FILE", __DIR__ . "/log/upstart.mtime");
}

if (!defined("UPSTART_TIME_SEND_WARNING")) {
    define("UPSTART_TIME_SEND_WARNING", 600);
}

if (!defined("AIDE_URL")) {
    define("AIDE_URL", "Aide/index");
}

if (!defined("TEMPLATE_PATH")) {
    define("TEMPLATE_PATH", __DIR__ . "/template/");
}

if (!defined("TIMEZONE")) {
    define("TIMEZONE", "Europe/Paris");
}


//Information pour le démon Pastell - les informations sont celles par défaut pour une Ubuntu 14.04LTS  et la plupart des sytêmes Linux
if (! defined("NB_WORKERS")) {
    define("NB_WORKERS", 5);
}

if (!defined("PHP_PATH")) {
    define("PHP_PATH", '/usr/local/bin/php');
}

if (!defined("PID_FILE")) {
    define("PID_FILE", WORKSPACE_PATH . "/pastell-daemon.pid");
}

if (!defined("DAEMON_LOG_FILE")) {
    define("DAEMON_LOG_FILE", WORKSPACE_PATH . "/pastell-daemon.log");
}

if (!defined("DAEMON_USER")) {
    define("DAEMON_USER", "www-data");
}

if (! defined("TESTING_ENVIRONNEMENT")) {
    define("TESTING_ENVIRONNEMENT", false);
}

if (!defined("LOG_ACTION_EXECUTOR_FACTORY_ERROR")) {
    define("LOG_ACTION_EXECUTOR_FACTORY_ERROR", false);
}

if (!defined("DISABLE_JOB_QUEUE")) {
    define("DISABLE_JOB_QUEUE", false);
}

if (!defined("DISABLE_JOURNAL_HORODATAGE")) {
    define("DISABLE_JOURNAL_HORODATAGE", false);
}

/** Permet de savoir ce que l'on va archiver dans la table journal_historique */
if (! defined("JOURNAL_MAX_AGE_IN_MONTHS")) {
    define("JOURNAL_MAX_AGE_IN_MONTHS", 2);
}

/* pour ne pas verrouiller les jobs qui ne se sont pas terminés correctement. */
/* suite à un arrêt brutal du serveur (ex: restart apache sans avoir arrêté le daemon avec des worker actifs) */
if (!defined("UNLOK_JOB_ERROR_AT_STARTUP")) {
    define("UNLOK_JOB_ERROR_AT_STARTUP", false);
}

date_default_timezone_set(TIMEZONE);

setlocale(LC_TIME, "fr_FR.UTF-8");


if (!defined("OCRE_RECEIVE_PASSPHRASE")) {
    define("OCRE_RECEIVE_PASSPHRASE", "changme");
}

if (! defined("OCRE_INPUT_DIRECTORY")) {
    define("OCRE_INPUT_DIRECTORY", "/data/workspace/ocre/");
}

/* Après NB_ENTITE_BEFORE_COLLAPSE entités, on utilise un composant de choix javascript */
if (! defined("NB_ENTITE_BEFORE_COLLAPSE")) {
    define("NB_ENTITE_BEFORE_COLLAPSE", 5);
}

/* Par défaut on utilise pas de serveur redis */
if (! defined("REDIS_SERVER")) {
    define("REDIS_SERVER", "");
}

if (! defined("REDIS_PORT")) {
    define("REDIS_PORT", 6379);
}

// Time to live des élements liste de flux, liste de connecteur, liste d'extension, rôle dans le cache Redis
// On ne relie pas les donnée de définition sur le disque quand ils sont dans le cache
// En développement, on mettra 1
// En production, on peut mettre 10 ou beaucoup plus
// Attention 0 signifie que le cache n'expire jamais !
// Mettre -1 pour désactiver le cache
if (! defined("CACHE_TTL_IN_SECONDS")) {
    define("CACHE_TTL_IN_SECONDS", 10);
}

if (!defined("LIBERSIGN_INSTALLER")) {
    define("LIBERSIGN_INSTALLER", "");
}


if (! defined("LOG_FILE")) {
    define("LOG_FILE", "/data/log/pastell.log");
}

if (! defined("LOG_LEVEL")) {
    define("LOG_LEVEL", Monolog\Logger::INFO);
}

if (empty($logger)) {
    if (empty($logger_system)) {
        $logger_system = "PASTELL";
    }
    $logger = new Monolog\Logger($logger_system);
    $logger->pushHandler(new Monolog\Handler\StreamHandler(LOG_FILE, LOG_LEVEL));
    $logger->pushProcessor(function ($record) {
        $record['extra']['pid'] = getmypid();
        return $record;
    });
}

if (!defined("UPLOAD_CHUNK_DIRECTORY")) {
    define("UPLOAD_CHUNK_DIRECTORY", "/data/upload_chunk");
}

# Nb Job / verrou
if (!defined("NB_JOB_PAR_VERROU")) {
    define("NB_JOB_PAR_VERROU", 1);
}

if (! defined("RGPD_PAGE_PATH")) {
    define("RGPD_PAGE_PATH", __DIR__ . "/template/RGPD.md");
}

if (!defined('LOGIN_PAGE_CONFIGURATION')) {
    define('LOGIN_PAGE_CONFIGURATION', 'login_page_configuration');
}

if (!defined('HTML_PURIFIER_CACHE_PATH')) {
    define('HTML_PURIFIER_CACHE_PATH', '/data/html_purifier');
}
if (!defined('LOGIN_PAGE_CONFIGURATION_LOCATION')) {
    define('LOGIN_PAGE_CONFIGURATION_LOCATION', WORKSPACE_PATH . DIRECTORY_SEPARATOR . LOGIN_PAGE_CONFIGURATION . '.json');
}

if (!defined('SENTRY_DSN')) {
    define('SENTRY_DSN', '');
}

/**
 * Si CONNECTEUR_DROIT est activé, alors l'accès au connecteur est géré par connecteur:lecture et connecteur:edition
 *   à la place de entite:lecture et entite:edition
 * @deprecated 3.0.4 : doit passé obligatoirement à true sur la prochaine version mineure
 */
if (!defined("CONNECTEUR_DROIT")) {
    define("CONNECTEUR_DROIT", false);
}

if (! defined("HTTP_PROXY_URL")) {
    define("HTTP_PROXY_URL", "");
}