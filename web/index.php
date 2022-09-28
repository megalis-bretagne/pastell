<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Pastell\Kernel;
use Symfony\Component\HttpFoundation\Request;

$logger_system = 'WEB';
require_once __DIR__ . '/../init.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

$request = Request::createFromGlobals();

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
