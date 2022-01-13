<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once __DIR__ . "/../../init.php";

/*
 *
 * NOTE IMPORTANTE  :
 *
 *  - Le script doit être passé uniquement sur une base de données déjà en V2.0.X
 * (c'est à dire après avoir passé au moins une fois le script dbupdate.php)
 *
 *  - Le script dbupdate.php doit être passé après celui-ci afin de recalculer les index des tables modifiées
 *
 */
$logger = $objectInstancier->getInstance(Logger::class);
$handler = new  StreamHandler('php://stdout');
$logger->pushHandler($handler);

$table_collation = $sqlQuery->getTablesCollation();

if (empty($table_collation['latin1_swedish_ci'])) {
    $logger->info("Aucune table n'est en latin1");
    exit;
}

$table_definition = json_decode(file_get_contents(__DIR__ . "/../../installation/pastell.bin"), true);

foreach ($table_collation['latin1_swedish_ci'] as $table_name) {
    $logger->info("La table $table_name est en latin1");

    foreach ($table_definition[$table_name]['Column'] as $column_name => $column_properties) {
        if ($column_properties['Type'] == 'datetime') {
            $logger->info("Correction de la table $table_name colonne $column_name");
            $sql = "update $table_name set $column_name='1970-01-01' where cast($column_name  as char(20))='0000-00-00 00:00:00';";
            $sqlQuery->query($sql);
        }
    }

    $logger->info("Passage de la table $table_name en utf-8");
    $sql = "ALTER TABLE $table_name CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    $sqlQuery->query($sql);
}
