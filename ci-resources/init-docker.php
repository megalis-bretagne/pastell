<?php


echo "Initialisation de Pastell";

require_once( __DIR__ . "/../init-no-db.php");
require_once( PASTELL_PATH . "/lib/dbupdate/DatabaseUpdate.class.php");


$sqlQuery = new SQLQuery(BD_DSN,BD_USER,BD_PASS);


$databaseUpdate = new DatabaseUpdate(file_get_contents(PASTELL_PATH."/installation/pastell.bin"),$sqlQuery);
$sqlCommand = $databaseUpdate->getDiff();

foreach($sqlCommand as $command){
	echo "Execution de la commande SQL : \n";
	echo "$command\n";
	$sqlQuery->query($command);

}

require_once __DIR__."/../init.php";

/** @var Utilisateur $utilisateur */
$utilisateur = $objectInstancier->Utilisateur;
if (! $utilisateur->getInfoByLogin('admin')){
	echo "L'utilisateur admin n'existe pas.\n";
	echo "Création de l'utilisateur admin/admin\n";
	$result = $objectInstancier->AdminControler->createAdmin('admin','admin','eric.pommateau@libriciel.coop');
}


#TODO installer l'horodateur interne
