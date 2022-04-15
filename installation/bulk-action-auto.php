<?php

//TODO  à supprimer
require_once(__DIR__ . "/../web/init.php");


if (count($argv) != 5) {
    echo "{$argv[0]} : Programme une action pour un ensemble de document\n";
    echo "Usage : {$argv[0]} id_e type_document etat_source etat_cible\n";
    exit;
}

$id_e = get_argv(1);
$type = get_argv(2);
$etat_source = get_argv(3);
$etat_cible = get_argv(4);

/** @var JobManager $jobManager */
$jobManager = $objectInstancier->getInstance(JobManager::class);

try {
    $jobManager->setTraitementParLotBulk($id_e, $type, $etat_source, $etat_cible);
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}
