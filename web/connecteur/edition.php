<?php
require_once( dirname(__FILE__) . "/../init-authenticated.php");
/** @var ConnecteurControler $connecteurControler */
$connecteurControler = $objectInstancier->{'ConnecteurControler'};
$connecteurControler->editionAction();
