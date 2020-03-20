<?php

/**
 * @deprecated use the console `bin/console app:add-default-frequencies`
 */

require_once __DIR__ . "/../../init.php";

/** @var PastellBootstrap $pastellBootstrap */
$pastellBootstrap = $objectInstancier->getInstance(PastellBootstrap::class);

$pastellBootstrap->installHorodateur();
