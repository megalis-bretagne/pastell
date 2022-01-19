<?php

/**
 * @var ObjectInstancier $objectInstancier
 */

require_once __DIR__ . '/../init.php';


if (isset($_SERVER['REMOTE_ADDR'])) {
    echo "Ce script n'est utilisable qu'en ligne de commande";
    exit;
}


$connecteur_info = $objectInstancier
    ->getInstance(FluxEntiteSQL::class)
    ->getConnecteur(0, 'global', "authentification");

if (!$connecteur_info) {
    echo "Il n'y a pas de connecteur cas-authentification associé dans les connecteur globaux.\n";
    exit;
}

$objectInstancier->getInstance(FluxEntiteSQL::class)->deleteConnecteur(0, 'global', "authentification");

echo "L'association global avec le connecteur cas-authentification a été supprimée.\n";
