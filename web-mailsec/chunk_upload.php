<?php

$logger_system = "MAILSEC";
require_once( __DIR__ . "/../init.php");


$objectInstancier->getInstance(FrontController::class)->getMailSecDestinataireControler()->chunkUploadAction();
