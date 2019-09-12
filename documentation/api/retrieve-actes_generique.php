<?php

require_once __DIR__."/DefaultSettings.php";
require_once __DIR__."/PastellSender.php";


if ($argc < 2){
	echo "Usage : {$argv[0]} id_d\n";
	echo "Permet de récupérer l'acquittement d'un document envoyé au contrôle de légalité\n";
	exit;
}

$id_d = $argv[1];

$pastellSender = new PastellSender(
	PASTELL_URL,
	PASTELL_LOGIN,
	PASTELL_PASSWORD,
	PASTELL_ID_E
);

try {
	echo "Récupération des informations du document $id_d\n";
	$result = $pastellSender->getInfo($id_d);
	print_r($result);

	if (empty($result['data']['aractes'][0])){
		echo "L'ARActes n'est pas encore disponibles\n";
		exit;
	}

	echo "Récupération du contenu de l'ARActes:\n";
	$aractes_content = $pastellSender->getFileContent($id_d,"aractes",0);
	echo $aractes_content;

} catch (PastellSenderException $e){

	echo "Un problème est survenu dans le déroulement de l'envoi de l'actes\n";
	echo $e->getMessage()."\n";
}