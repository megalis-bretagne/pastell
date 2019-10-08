<?php
$logger_system = "API";
require_once __DIR__."/../../init.php";
$api = new HttpApi($objectInstancier);
$api->setGetArray($_GET);
$api->setRequestArray($_REQUEST);
$api->setServerArray($_SERVER);
$api->dispatch();
