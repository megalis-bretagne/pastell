#! /usr/bin/php
<?php

/**
 * @var ObjectInstancier $objectInstancier
 */

require_once dirname(__FILE__) . '/../init.php';

$id_ce = $objectInstancier->getInstance(ConnecteurEntiteSQL::class)->getGlobal('iParapheur');
$objectInstancier->getInstance(ActionExecutorFactory::class)->executeOnConnecteur($id_ce, 0, 'update-all-iparapheur');

echo $objectInstancier->getInstance(ActionExecutorFactory::class)->getLastMessage();
