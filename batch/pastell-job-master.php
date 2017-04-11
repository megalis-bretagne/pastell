<?php

require_once( __DIR__ . "/../init.php");

/** @var PastellDaemon $pastellDaemon */
$pastellDaemon = $objectInstancier->getInstance("PastellDaemon");
$pastellDaemon->jobMaster();
