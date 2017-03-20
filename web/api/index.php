<?php

require_once __DIR__."/../../init.php";
$api = new HTTP_API($objectInstancier);
$api->setGetArray($_GET);
$api->setRequestArray($_REQUEST);
$api->setServerArray($_SERVER);
$api->dispatch();
