<?php

/** @deprecated Since 4.0.3, Use app:daemon:start-worker command instead */

trigger_error('Since 4.0.3, Use app:daemon:start-worker command instead', E_USER_DEPRECATED);

/**
 * @var ObjectInstancier $objectInstancier
 */

$logger_system = "WORKER";
require_once __DIR__ . '/../init.php';

/** @var PastellDaemon $pastellDaemon */
$pastellDaemon = $objectInstancier->getInstance(PastellDaemon::class);
$pastellDaemon->runningWorker();
