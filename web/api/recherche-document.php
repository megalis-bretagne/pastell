<?php

require_once __DIR__."/../../init.php";
$api = new ApiController($objectInstancier);
$api->callJson('Document','recherche');

