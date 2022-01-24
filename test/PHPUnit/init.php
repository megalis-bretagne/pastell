<?php

ini_set('MAX_EXECUTION_TIME', -1);

error_reporting(E_ALL);

define("TESTING_ENVIRONNEMENT", true);

require_once __DIR__ . '/../../ci-resources/define-from-environnement.php';
require_once __DIR__ . '/../../init-no-db.php';

function pastell_autoload($class_name)
{
    $result = @ include_once($class_name . '.class.php');
    if (! $result) {
        return false;
    }
    return true;
}

$sqlQuery = new SQLQuery(BD_DSN_TEST, BD_USER_TEST, BD_PASS_TEST);

$sqlQuery->query('set SQL_MODE="NO_ENGINE_SUBSTITUTION";');

$database_file = PASTELL_PATH . "/installation/pastell.bin";

$databaseUpdate = new DatabaseUpdate(file_get_contents($database_file), $sqlQuery);
$sqlCommand = $databaseUpdate->getDiff();

foreach ($sqlCommand as $sql) {
    $sqlQuery->query($sql);
}
$daemon_command = PHP_PATH . " " . realpath(__DIR__ . "/../../batch/pastell-job-master.php");

$daemonManger = new DaemonManager($daemon_command, PID_FILE, DAEMON_LOG_FILE, DAEMON_USER);
$daemonManger->stop();
