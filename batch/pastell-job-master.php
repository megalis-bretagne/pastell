<?php

/** @deprecated Since 4.0.3, Use app:daemon:start command instead */

trigger_error('Since 4.0.3, Use app:daemon:start command instead', E_USER_DEPRECATED);

$logger_system = "DAEMON";

require_once __DIR__ . '/../init.php';

$pastellDaemon = ObjectInstancierFactory::getObjetInstancier()->getInstance(PastellDaemon::class);

pcntl_signal(SIGTERM, fn() => $pastellDaemon->stop());

$pastellDaemon->jobMaster();
