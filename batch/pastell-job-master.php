<?php

$logger_system = "DAEMON";

require_once __DIR__ . '/../init.php';

$pastellDaemon = ObjectInstancierFactory::getObjetInstancier()->getInstance(PastellDaemon::class);

pcntl_signal(SIGTERM, fn() => $pastellDaemon->stop());

$pastellDaemon->jobMaster();
