<?php

use Monolog\Logger;
use Pastell\Database\DatabaseUpdater;

try {
    echo "Initialisation de Pastell [DOCKER]\n";

# Première étape : Sans la connexion BD vu que celle-ci n'existe pas encore...
    require_once(__DIR__ . "/../init-no-db.php");
    /**
     * @var Logger $logger
     */

    echo "Utilisation de la base " . BD_DSN . " avec l'utilisateur " . BD_USER . "\n";

    $sqlQuery = new SQLQuery(BD_DSN, BD_USER, BD_PASS);

    $sqlQuery->waitStarting(function ($message) {
        echo "[" . date("Y-m-d H:i:s") . "][Pastell - wait for MySL] $message\n";
    });
    $databaseUpdater = new DatabaseUpdater($sqlQuery, $logger);
    $databaseUpdater->update();

# Deuxième étape : initialisation normale de Pastell
    require_once __DIR__ . '/../init.php';
    $pastellBootstrap = ObjectInstancierFactory::getObjetInstancier()->getInstance(PastellBootstrap::class);
    $pastellBootstrap->bootstrap();
} catch (Exception $e) {
    echo $e->getMessage();
}
