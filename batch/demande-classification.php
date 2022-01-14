#! /usr/bin/php
<?php

require_once(__DIR__ . "/../init.php");

/** @var ConnecteurEntiteSQL $connecteurEntiteSQL */
$connecteurEntiteSQL = $objectInstancier->getInstance(ConnecteurEntiteSQL::class);

/** @var ActionExecutorFactory $actionExecutorFactory */
$actionExecutorFactory = $objectInstancier->getInstance(ActionExecutorFactory::class);

$id_ce = $connecteurEntiteSQL->getOne('s2low');
$result = $actionExecutorFactory->executeOnConnecteur($id_ce, 0, 'demande-classification');
