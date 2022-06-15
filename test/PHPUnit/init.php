<?php

ini_set('MAX_EXECUTION_TIME', -1);

error_reporting(E_ALL);

define("TESTING_ENVIRONNEMENT", true);


require_once __DIR__ . '/../../docker/define-from-environnement.php';
require_once __DIR__ . '/../../init-no-db.php';

$sqlQuery = new SQLQuery(BD_DSN_TEST, BD_USER_TEST, BD_PASS_TEST);

$sqlQuery->query('set SQL_MODE="NO_ENGINE_SUBSTITUTION";');

$database_file = PASTELL_PATH . "/installation/pastell.bin";

$databaseUpdate = new DatabaseUpdate(file_get_contents($database_file), $sqlQuery);
$sqlCommand = $databaseUpdate->getDiff();

foreach ($sqlCommand as $sql) {
    $sqlQuery->query($sql);
}
