<?php



$documentEntite = new DocumentEntite($sqlQuery);
$id_ged = $documentEntite->getEntiteWithRole($id_d,"editeur");

$actionCreator = new ActionCreator($sqlQuery,$journal,$id_d);
$actionCreator->addAction($id_e,$authentification->getId(),$action, "Vous avez accus� r�ception de ce message");
$actionCreator->addToEntite($id_ged,"Un accus� de r�ception a �t� recu pour le document");

$message = "Un accus� de r�ception a �t� recu pour le document $id_d ";
$notificationMail->notify($id_ged,$id_d,$action, 'rh-message',$message);


$lastMessage->setLastMessage("L'accus� de r�ception a �t� envoy� au centre de gestion");

header("Location: detail.php?id_d=$id_d&id_e=$id_e");