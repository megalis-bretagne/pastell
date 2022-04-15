<?php
//TODO à mettre dans un connecteur global #1356

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once(__DIR__ . "/../init.php");
$handler = new  StreamHandler('php://stdout');
$objectInstancier->getInstance(Logger::class)->pushHandler($handler);
$journalManager = $objectInstancier->getInstance(JournalManager::class);
$result = $journalManager->purgeToHistorique();
exit($result ? 0 : -1);
