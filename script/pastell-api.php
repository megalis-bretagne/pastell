<?php

require_once __DIR__ . "/../init.php";

/** @var InternalAPI $internalAPI */
$internalAPI = $objectInstancier->getInstance(InternalAPI::class);
$internalAPI->setCallerType(InternalAPI::CALLER_TYPE_SCRIPT);

$result = $internalAPI->get($argv[1]);

/** @var JSONoutput $jsonOutput */
$jsonOutput = $objectInstancier->getInstance(JsonOutput::class);
echo $jsonOutput->getJson($result, true) . "\n";
