<?php

require_once( PASTELL_PATH . "/lib/entite/EntiteRelation.class.php");
require_once (PASTELL_PATH . "/lib/entite/EntiteProperties.class.php");


$entite = new Entite($sqlQuery,$id_e);
$infoEntite = $entite->getInfo();

$id_cdg = $infoEntite['centre_de_gestion'];

if (!$id_cdg){
	$lastError->setLastError("La collectivit� n'a pas de centre de gestion");
	header("Location: detail.php?id_d=$id_d&id_e=$id_e");
	exit;
}

$documentEntite = new DocumentEntite($sqlQuery);
$documentEntite->addRole($id_d,$id_cdg,"lecteur");

$id_u = 0;

if ($authentification->isConnected()){
	$id_u = $authentification->getId();
} 

$actionCreator = new ActionCreator($sqlQuery,$journal,$id_d);

$actionCreator->addAction($id_e,$id_u,'send-cdg',"Le document a �t� envoy� au centre de gestion");
$actionCreator->addToEntite($id_cdg,"Le document a �t� envoy� par la collectivit�");

$actionCreator->addAction($id_cdg,0,'recu-cdg',"Le document a �t� re�u par le centre de gestion");
$actionCreator->addToEntite($id_e,"Le document a �t� re�u par le centre de gestion");


$message =  "La transaction $id_d est pass� dans l'�tat :  " . $theAction->getActionName('send-cdg');
$message .= "\n\n";

$notificationMail->notify($id_cdg,$id_d,'recu-cdg', 'rh-actes',$message);


$entiteProperties = new EntiteProperties($sqlQuery,$id_cdg);
$has_ged = $entiteProperties->getProperties(EntiteProperties::ALL_FLUX,'has_ged');
if ($has_ged == 'auto'){	
	$actionCreator->addAction($id_cdg,0,'send-ged',"Le document a �t� d�pos� dans la GED");
}

$has_archivage = $entiteProperties->getProperties(EntiteProperties::ALL_FLUX,'has_archivage');
if ($has_archivage == 'auto'){	
	$actionCreator->addAction($id_cdg,0,'send-archive',"Le document a �t� archiv�");
}

if ($authentification->isConnected()){
	$lastMessage->setLastMessage("Le document a �t� envoy� � votre centre de gestion");
	header("Location: detail.php?id_d=$id_d&id_e=$id_e");
}