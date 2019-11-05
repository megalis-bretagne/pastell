<?php

if (! file_exists(__DIR__ . "/LocalSettings.php")) {
    echo <<<EOT
Vous devez créer un fichier LocalSettings.php de cette forme afin de préciser les informations pour accéder à Pastell


<?php
define("PASTELL_URL","https://pastell.partenaires.libriciel.fr"); //URL du serveur Pastell
define("PASTELL_LOGIN","userdemo");
define("PASTELL_PASSWORD","XXXX");
define("PASTELL_ID_E","34"); //Identifiant de l'entité Pastell



EOT;
    exit;
}

require_once __DIR__ . "/LocalSettings.php";
