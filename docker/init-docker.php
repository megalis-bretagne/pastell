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

# Deuxième étape : initialisation normal de Pastell
    require_once __DIR__ . "/../init.php";

    /** @var PastellBootstrap $pastellBootstrap */
    $pastellBootstrap = ObjectInstancierFactory::getObjetInstancier()->getInstance(PastellBootstrap::class);

    $envWrapper = new EnvWrapper();
    $utilisateurObject = new UtilisateurObject();
    $utilisateurObject->login = $envWrapper->get('PASTELL_ADMIN_LOGIN', 'admin');
    $utilisateurObject->password = $envWrapper->get('PASTELL_ADMIN_PASSWORD', 'admin');
    $utilisateurObject->email = $envWrapper->get('PASTELL_ADMIN_EMAIL', 'test@libriciel.net');
    $pastellBootstrap->bootstrap($utilisateurObject);
} catch (Exception $e) {
    echo $e->getMessage();
}
