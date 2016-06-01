<?php
require_once( __DIR__ . "/../web/init.php");

if (count($argv) < 4){
	echo "{$argv[0]} : Modifie en masse tous les connecteurs du m�me type en mettant une nouvelle valeur\n";
	echo "Usage : {$argv[0]} connecteur champs nouvelle_valeur\n";
	exit;
}

$connecteur = get_argv(1);
$champs = get_argv(2);
$valeur = get_argv(3);


/** @var ConnecteurEntiteSQL $connecteurEntiteSQL */
$connecteurEntiteSQL = $objectInstancier->{'ConnecteurEntiteSQL'};

$all = $connecteurEntiteSQL->getAllById($connecteur);


foreach($all as $connecteur){
	echo $connecteur['id_ce']." ".$connecteur['libelle']."\n";
}

echo count($all)." connecteurs vont �tre mis � jour sans possibilit� de revenir en arri�re\n";
echo " \n";


$rep = readline("�tes-vous s�r ? (OUI/non) : ");

if ($rep != 'OUI'){
	exit;
}

/** @var ConnecteurFactory $connecteurFactory */
$connecteurFactory = $objectInstancier->{'ConnecteurFactory'};

foreach($all as $connecteur){
	$connecteurConfig = $connecteurFactory->getConnecteurConfig($connecteur['id_ce']);

	$old_value = $connecteurConfig->get($champs);
	$connecteurConfig->setData($champs, $valeur);

	echo "{$connecteur['id_ce']} {$connecteur['libelle']} : $old_value => $valeur\n";

}



function readline($prompt = null){
	if($prompt){
		echo $prompt;
	}
	$fp = fopen("php://stdin","r");
	$line = rtrim(fgets($fp, 1024));
	return $line;
}