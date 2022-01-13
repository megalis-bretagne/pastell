<?php

try {
    echo "Initialisation de Pastell [DOCKER]\n";

# Première étape : Sans la connexion BD vu que celle-ci n'existe pas encore...
    require_once(__DIR__ . "/../init-no-db.php");

    echo "Utilisation de la base " . BD_DSN . " avec l'utilisateur " . BD_USER . "\n";

    $sqlQuery = new SQLQuery(BD_DSN, BD_USER, BD_PASS);

    $sqlQuery->waitStarting(function ($message) {
        echo "[" . date("Y-m-d H:i:s") . "][Pastell - wait for MySL] $message\n";
    });
    $databaseUpdate = new DatabaseUpdate(file_get_contents(__DIR__ . "/../installation/pastell.bin"), $sqlQuery);
    $databaseUpdate->majDatabase(
        $sqlQuery,
        function ($message) {
            echo "[" . date("Y-m-d H:i:s") . "][Pastell - SQL init] $message\n";
        }
    );

# Deuxième étape : initialisation normal de Pastell
    require_once __DIR__ . "/../init.php";

    /** @var PastellBootstrap $pastellBootstrap */
    $pastellBootstrap = $objectInstancier->getInstance(PastellBootstrap::class);

    $envWrapper = new EnvWrapper();
    $utilisateurObject = new UtilisateurObject();
    $utilisateurObject->login = $envWrapper->get('PASTELL_ADMIN_LOGIN', 'admin');
    $utilisateurObject->password = $envWrapper->get('PASTELL_ADMIN_PASSWORD', 'admin');
    $utilisateurObject->email = $envWrapper->get('PASTELL_ADMIN_EMAIL', 'noreply@libriciel.coop');

    $pastellBootstrap->bootstrap($utilisateurObject);
} catch (Exception $e) {
    echo $e->getMessage();
}
