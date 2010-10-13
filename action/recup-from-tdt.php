<?php
require_once (PASTELL_PATH . "/lib/document/DocumentEntite.class.php");
require_once (PASTELL_PATH . "/lib/action/DocumentActionEntite.class.php");

require_once (PASTELL_PATH . "/lib/document/Document.class.php");
require_once (PASTELL_PATH . "/lib/document/DocumentType.class.php");


$document = new Document($sqlQuery);
$infoDocument = $document->getInfo($id_d);
		
$actionCreator = new ActionCreator($sqlQuery,$journal,$id_d);
$actionCreator->addAction($id_e,0,'acquiter-tdt',"Le document $id_d a �t� acquitt� par le contr�le de l�galit�");
	

$message = "Le document $id_d a �t� acquitt� par le contr�le de l�galit�";
$notificationMail->notify($id_e,$id_d,'acquiter-tdt', 'rh-actes',$message);
	
$theAction = $documentTypeFactory->getDocumentType($infoDocument['type'])->getAction();
include( dirname(__FILE__) . "/envoyer_au_cdg.php");
	
