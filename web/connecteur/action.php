<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");

$recuperateur = new Recuperateur($_POST);


$action = $recuperateur->get('action');
$id_ce = $recuperateur->getInt('id_ce',0);

$actionPossible = $objectInstancier->ActionPossible;

if ( ! $actionPossible->isActionPossibleOnConnecteur($id_ce,$authentification->getId(),$action)) {
	$objectInstancier->LastError->setLastError("L'action « $action »  n'est pas permise : " .$actionPossible->getLastBadRule() );
	header("Location: edition?id_ce=$id_ce");
	exit;
}



$result = $objectInstancier->ActionExecutorFactory->executeOnConnecteur($id_ce,$authentification->getId(),$action);

$message = $objectInstancier->ActionExecutorFactory->getLastMessage();

if (! $result ){
	$objectInstancier->LastError->setLastError($message);	
} else {
	$objectInstancier->LastMessage->setLastMessage($message);	
}

header("Location: edition?id_ce=$id_ce");
