<?php

require_once( PASTELL_PATH . "/lib/system/Tedetis.class.php");

$collectviteProperties = new DonneesFormulaire(WORKSPACE_PATH  . "/$id_e.yml");

$tedetis = new Tedetis($collectviteProperties);


if (!  $tedetis->postActes($donneesFormulaire) ){
	$lastError->setLastError( $tedetis->getLastError());
	header("Location: detail.php?id_d=$id_d&id_e=$id_e");
	exit;
}


$actionCreator = new ActionCreator($sqlQuery,$journal,$id_d);
$actionCreator->addAction($id_e,$authentification->getId(),$action,"Le document a �t� envoy� au contr�le de l�galit�");
	

$lastMessage->setLastMessage("Le document a �t� envoy� au contr�le de l�galit�");
	
header("Location: detail.php?id_d=$id_d&id_e=$id_e");