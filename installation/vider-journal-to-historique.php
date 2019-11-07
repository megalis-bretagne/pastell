<?php
require_once(__DIR__ . "/../init.php");

$handler = new  Monolog\Handler\StreamHandler('php://stdout');
$objectInstancier->getInstance('Monolog\Logger')->pushHandler($handler);

$journalManager = $objectInstancier->getInstance(JournalManager::class);
$result = $journalManager->purgeToHistorique();
exit($result ? 0 : -1);
