<?php

/**
 * TODO: Remove in 4.0
 */

/* Script à executer pour le passage 1.3 => 1.4 */
/* Le script peut être executer plusieurs fois sans problème */

require_once(__DIR__ . "/../../init.php");

echo "Script passage de la v1.3.x à la version 1.4\n";


echo "Inscription des jobs pour les document: \n";
$documentEntite = new DocumentEntite($sqlQuery);
foreach ($objectInstancier->fluxDefinitionFiles->getAll() as $type => $config) {
    $tabAction = $objectInstancier->DocumentTypeFactory->getFluxDocumentType($type)->getAction()->getAutoAction();
    foreach ($tabAction as $etat_actuel => $etat_cible) {
        foreach ($documentEntite->getFromAction($type, $etat_actuel) as $infoDocument) {
            echo "Ajout de ({$infoDocument['id_e']},{$infoDocument['id_d']},{$infoDocument['type']},$etat_actuel->$etat_cible) : ";
            try {
                $objectInstancier->JobManager->setJobForDocument($infoDocument['id_e'], $infoDocument['id_d'], "Inscription suite au passage en v1.4");
            } catch (Exception $e) {
                echo "FAILED - {$e->getMessage()}";
                continue;
            }
            echo "OK\n";
        }
    }
}


echo "Inscription des jobs pour les connecteurs globaux: \n";
$connecteur_list = $objectInstancier->ConnecteurEntiteSQL->getAll(0);
foreach ($connecteur_list as $connecteur_info) {
    echo "GLOBAL - {$connecteur_info['libelle']} - id_ce : {$connecteur_info['id_ce']}: ";
    try {
        $objectInstancier->JobManager->setJobForConnecteur($connecteur_info['id_ce'], "Inscription suite au passage en v1.4");
    } catch (Exception $e) {
        echo "FAILED - {$e->getMessage()}";
        continue;
    }
    echo "OK\n";
}

echo "Inscription des jobs pour les connecteurs d'entité: \n";
$connecteur_list = $objectInstancier->ConnecteurEntiteSQL->getAllLocal();
foreach ($connecteur_list as $connecteur_info) {
    echo "Id_e: {$connecteur_info['id_e']} - {$connecteur_info['libelle']} - id_ce : {$connecteur_info['id_ce']}: ";
    try {
        $objectInstancier->JobManager->setJobForConnecteur($connecteur_info['id_ce'], "Inscription suite au passage en v1.4");
    } catch (Exception $e) {
        echo "FAILED - {$e->getMessage()}";
        continue;
    }
    echo "OK\n";
}
