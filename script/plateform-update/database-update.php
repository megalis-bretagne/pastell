<?php

require_once(dirname(__FILE__) . "/../../init.php");
require_once(PASTELL_PATH . "/lib/dbupdate/DatabaseUpdate.class.php");

$objectInstancier = ObjectInstancierFactory::getObjetInstancier();
$sqlQuery = $objectInstancier->getInstance(SQLQuery::class);

$do = get_argv(1);

if (! file_exists(DATABASE_FILE)) {
    echo "Le fichier " . DATABASE_FILE . " contenant la définition de la base de données n'existe pas !\n";
    exit(1);
}

$databaseUpdate = new DatabaseUpdate(file_get_contents(DATABASE_FILE), $sqlQuery);
$sqlCommand = $databaseUpdate->getDiff();

try {
    foreach ($sqlCommand as $command) {
        echo "$command\n";
        if ($do == 'do') {
            $sqlQuery->query($command);
        }
    }
} catch (Exception $e) {
    echo "Erreur while executing command : $command \n";
    echo $e->getMessage() . "\n";
    exit(1);
}

exit($sqlCommand ? 1 : 0);
