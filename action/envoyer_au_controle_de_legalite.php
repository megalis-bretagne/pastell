<?php

$actionCreator = new ActionCreator($sqlQuery,$journal,$id_d);
$actionCreator->addAction($id_e,$authentification->getId(),$action,"Le document a �t� envoy� au contr�le de l�galit�");
	

$lastMessage->setLastMessage("Le document a �t� envoy� au contr�le de l�galit�");
	
header("Location: detail.php?id_d=$id_d&id_e=$id_e");