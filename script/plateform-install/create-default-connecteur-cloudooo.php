<?php

require_once __DIR__ . "/../../init.php";


/** @var PastellBootstrap $pastellBootstrap */
$pastellBootstrap = $objectInstancier->getInstance(PastellBootstrap::class);

$pastellBootstrap->installCloudooo("localhost");
