#! /usr/bin/php
<?php

require_once(__DIR__ . "/../init.php");

$flux = get_argv(1);

if (!$flux) {
    echo "Usage : {$argv[0]} flux\n";
    exit;
}

$result = $objectInstancier->Document->getAllByType($flux);

if (!$result) {
    echo "Il n'y a pas de document de type $flux\n";
    exit;
}

$all_job = [];
foreach ($result as $document) {
    $id_d = $document['id_d'];
    $doc_entite = $objectInstancier->DocumentEntite->getEntite($id_d);
    foreach ($doc_entite as $entite) {
        $id_e = $entite['id_e'];
        $id_job = $objectInstancier->JobQueueSQL->getJobIdForDocument($id_e, $id_d);
        if ($id_job) {
            echo 'Entite: ' . "{$id_e}" . ', document: ' . "{$id_d} \n";
            echo 'Le job: ' . "{$id_job} sera supprimé pour ce document\n";
            $all_job[] = $id_job;
        }
    }
}

if (! $all_job) {
    echo "Pas de job a supprimer...\n\n";
    exit;
}

$nb_job = count($all_job);
echo "\n$nb_job jobs vont être supprimés !\n\n";


echo "Etes-vous sur (o/N) ? ";
$fh = fopen('php://stdin', 'r');
$entree = trim(fgets($fh, 1024));

if ($entree != 'o') {
    exit;
}

foreach($all_job as $id_job) {
    $objectInstancier->JobQueueSQL->deleteJob($id_job);
    echo "Le job $id_job a été supprimé\n";
}


echo "Les jobs ont ete supprimés\n";
