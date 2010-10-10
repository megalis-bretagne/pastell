<?php

require_once (PASTELL_PATH . "/lib/action/DocumentAction.class.php");



$documentEntite = new DocumentEntite($sqlQuery);
$id_col = $documentEntite->getEntiteWithRole($id_d,"editeur");

$documentAction = new DocumentAction($sqlQuery,$journal,$id_d,$id_e,$authentification->getId());
$id_a = $documentAction->addAction($action);

$documentActionEntite = new DocumentActionEntite($sqlQuery);
$documentActionEntite->addAction($id_a,$id_e,$journal);
$documentActionEntite->addAction($id_a,$id_col,$journal);

$message = "Un accus� de r�ception a �t� recu pour le document $id_d ";
$notificationMail->notify($id_col,$id_d,$action, 'rh-message',$message);


$lastMessage->setLastMessage("L'accus� de r�ception a �t� envoy� au centre de gestion");

header("Location: detail.php?id_d=$id_d&id_e=$id_e");