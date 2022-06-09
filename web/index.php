<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Pastell\Kernel;

$logger_system = "WEB";
require_once(__DIR__."/../init.php");


(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');


$request = Request::createFromGlobals();

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$response = $kernel->handle($request);
if ($response->isNotFound() === false) {
    $response->send();
    $kernel->terminate($request, $response);
} else {

    $frontController = new FrontController($objectInstancier);

    $frontController->setGetParameter($_GET);
    $frontController->setPostParameter($_POST);
    $frontController->setServerInfo($_SERVER);

    $frontController->dispatch();
}