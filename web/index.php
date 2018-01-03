<?php

$logger_system = "WEB";
require_once(__DIR__."/../init.php");

$frontController = new FrontController($objectInstancier);

$frontController->setGetParameter($_GET);
$frontController->setPostParameter($_POST);
$frontController->setServerInfo($_SERVER);

$frontController->dispatch();