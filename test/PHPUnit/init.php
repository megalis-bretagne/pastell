<?php

use Pastell\Database\DatabaseUpdater;

ini_set('MAX_EXECUTION_TIME', -1);

error_reporting(E_ALL);

define("TESTING_ENVIRONNEMENT", true);


require_once __DIR__ . '/../../docker/define-from-environnement.php';
require_once __DIR__ . '/../../init-no-db.php';

$sqlQuery = new SQLQuery(BD_DSN_TEST, BD_USER_TEST, BD_PASS_TEST);

$sqlQuery->query('set SQL_MODE="NO_ENGINE_SUBSTITUTION";');

$databaseUpdater = new DatabaseUpdater($sqlQuery, $logger);
$databaseUpdater->update();
