<?php

require_once __DIR__ . "/../init.php";

/** @var InternalAPI $internalAPI */
$internalAPI = $objectInstancier->getInstance("InternalAPI");
$internalAPI->setCallerType(InternalAPI::CALLER_TYPE_SCRIPT);

$result = $internalAPI->get($argv[1]);

/** @var JSONoutput $jsonOutput */
$jsonOutput = $objectInstancier->getInstance("JsonOutput");
echo $jsonOutput->getJson($result, true) . "\n";
