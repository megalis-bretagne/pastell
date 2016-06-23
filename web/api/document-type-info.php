<?php
require_once __DIR__."/../../init.php";
$api = new ApiController($objectInstancier);
$api->callJson('DocumentType','info');


/*require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
$type = $recuperateur->get('type');

$api_json->documentTypeInfo($type);*/

