<?php
require_once(dirname(__FILE__) . "/../init.php");
require_once(PASTELL_PATH . "/lib/dbupdate/DatabaseUpdate.class.php");

$databaseUpdate = new DatabaseUpdate(false, $sqlQuery);
$databaseUpdate->writeDefinition(DATABASE_FILE, __DIR__ . "/pastell.sql");
