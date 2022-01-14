<?php

$logger_system = "DAEMON";

require_once(__DIR__ . "/../init.php");

/** @var PastellDaemon $pastellDaemon */
$pastellDaemon = $objectInstancier->getInstance(PastellDaemon::class);
$pastellDaemon->jobMaster();
