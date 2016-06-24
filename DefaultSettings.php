<?php 
 
//Ce fichier contient les valeurs par défaut

if (file_exists( __DIR__ . "/LocalSettings.php")){
	//Il est possible d'écraser les valeurs par défaut en
	//créant un fichier LocalSettings.php
	require_once( __DIR__ . "/LocalSettings.php");
}

if (! defined("PASTELL_PATH")){
	define("PASTELL_PATH",__DIR__ ."/");
}

if (! defined("ADMIN_EMAIL")){
	define("ADMIN_EMAIL","mettre_un_email");
}

//Emplacement du répertoire pour sauvegarder les fichiers temporaires
//ATTENTION : CE RÉPERTOIRE DOIT ÊTRE ACCESSIBLE EN ECRITURE
if (!defined("WORKSPACE_PATH")){
	define("WORKSPACE_PATH" , PASTELL_PATH . "/workspace");
}

//Définition de la connexion à la base de données
if (!defined("BD_DSN")){
	define("BD_DSN","mysql:dbname=pastell;host=127.0.0.1;port=3306;charset=utf8");
}
if (!defined("BD_USER")){
	define("BD_USER","pastell");
}
if (!defined("BD_PASS")){
	define("BD_PASS","pastell");
}


//Définition de la connexion à la base de données pour les tests unitaires et les tests de validation
if (!defined("BD_DSN_TEST")){
	define("BD_DSN_TEST","mysql:dbname=pastell_test;host=localhost;port=8889");
}
if (!defined("BD_USER_TEST")){
	define("BD_USER_TEST","user");
}
if (!defined("BD_PASS_TEST")){
	define("BD_PASS_TEST","user");
}
if (!defined("BD_DBNAME_TEST")){
	define("BD_DBNAME_TEST","pastell_test");
}



//Attention, il faut une version d'openSSL > 1.0.0a 
if (! defined("OPENSSL_PATH")){
	//Une mauvaise définition du chemin entraîne une erreur fatale lors de la tentative de connexion.
	//OpenSSL est OBLIGATOIRE sur Pastell
	define("OPENSSL_PATH","/usr/bin/openssl");
}

//Racine du site Pastell
//ex : http://pastell.sigmalis.com/
//ex : http://www.sigmalis.com/pastell/
//Toujours finir l'adresse par un /
if (!defined("SITE_BASE")){
	define("SITE_BASE","http://192.168.1.5/adullact/pastell/web/");
}

if (!defined("WEBSEC_BASE")){
	define("WEBSEC_BASE","http://192.168.1.5/adullact/pastell/web-mailsec/");
}

if (!defined("AGENT_FILE_PATH")){
	define("AGENT_FILE_PATH","/tmp/agent");
}
if (! defined("PRODUCTION")){
	define("PRODUCTION",false);
}

if (!defined("PLATEFORME_MAIL")){
	define("PLATEFORME_MAIL","pastell@sigmalis.com");
}

if (!defined("UPSTART_TOUCH_FILE")){
	define("UPSTART_TOUCH_FILE",__DIR__."/log/upstart.mtime");
}

if (!defined("UPSTART_TIME_SEND_WARNING")){
	define("UPSTART_TIME_SEND_WARNING",600);
}

if (!defined("AIDE_URL")){
	define("AIDE_URL","aide/index.php");
}

if (!defined("TEMPLATE_PATH")){
	define("TEMPLATE_PATH",__DIR__."/template/");
}

if (!defined("TIMEZONE")){
	define("TIMEZONE","Europe/Paris");
}


//Information pour le démon Pastell - les informations sont celles par défaut pour une Ubuntu 14.04LTS  et la plupart des sytêmes Linux
if (! defined("NB_WORKERS")){
	define("NB_WORKERS",5);
}

if (!defined("PHP_PATH")){
	define("PHP_PATH","/usr/bin/php");
}

if (!defined("PID_FILE")){
	define("PID_FILE",PASTELL_PATH . "/daemon/pastell-daemon.pid");
}

if (!defined("DAEMON_LOG_FILE")){
	define("DAEMON_LOG_FILE",PASTELL_PATH . "/daemon/pastell-daemon.log");
}

if (!defined("DAEMON_USER")){
	define("DAEMON_USER","www-data");
}

if (! defined("TESTING_ENVIRONNEMENT")){
	define("TESTING_ENVIRONNEMENT",false);
}

if (!defined("LOG_ACTION_EXECUTOR_FACTORY_ERROR")){
	define("LOG_ACTION_EXECUTOR_FACTORY_ERROR",false);
}

if (! defined("LOG_FILE")){
	define("LOG_FILE", "/dev/null");
}

if (!defined("DISABLE_JOB_QUEUE")){
	define("DISABLE_JOB_QUEUE",false);
}

if (!defined("DISABLE_JOURNAL_HORODATAGE")){
	define("DISABLE_JOURNAL_HORODATAGE",false);
}

date_default_timezone_set(TIMEZONE);

setlocale(LC_TIME,"fr_FR.ISO8859-15");