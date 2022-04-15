#! /usr/bin/php
<?php
// TODO à supprimer

/**
 * @var ObjectInstancier $objectInstancier
 */

require_once __DIR__ . '/../init.php';

trigger_error(
    'Since 3.1.10 | Removed in 4.0.0 | Use bin/console app:connector:delete-by-type as replacement',
    \E_USER_DEPRECATED
);

$id_connecteur = get_argv(1);

if (!$id_connecteur) {
    echo "Usage : {$argv[0]} id_connecteur\n";
    exit;
}

$result = $objectInstancier->ConnecteurEntiteSQL->getAllByConnecteurId($id_connecteur);

if (!$result) {
    echo "Il n'y a pas de connecteur de type $id_connecteur\n";
    exit;
}

foreach ($result as $connecteur) {
    echo "\n";
    echo 'Entite: ' . "{$connecteur['id_e']}" . ', connecteur: ' . "{$connecteur['id_ce']} \n";


    $job_list = $objectInstancier->WorkerSQL->getJobListWithWorkerForConnecteur($connecteur['id_ce']);
    foreach ($job_list as $job) {
        echo 'Avec Entite: ' . "{$job['id_e']}" . ', id_job: ' . "{$job['id_job']} \n";
    }
    $nb_job = count($job_list);
    echo "$nb_job jobs vont être supprimés pour ce connecteur\n";


    $flux_list = $objectInstancier->FluxEntiteSQL->getUsedByConnecteur($connecteur['id_ce']);
    foreach ($flux_list as $flux) {
        echo 'Avec Entite: ' . "{$flux['id_e']}" . ', association flux: ' . "{$flux['flux']} \n";
    }
    $nb_flux = count($flux_list);
    echo "$nb_flux association de flux vont être supprimés pour ce connecteur\n";
    echo "\n";
}
$nb_connecteur = count($result);
echo "$nb_connecteur connecteurs vont être supprimés !\n";


echo "Etes-vous sur (o/N) ? ";
$fh = fopen('php://stdin', 'r');
$entree = trim(fgets($fh, 1024));

if ($entree != 'o') {
    exit;
}
foreach ($result as $connecteur) {
    $job_list = $objectInstancier->WorkerSQL->getJobListWithWorkerForConnecteur($connecteur['id_ce']);
    foreach ($job_list as $job) {
        $objectInstancier->JobQueueSQL->deleteJob($job['id_job']);
    }

    $flux_list = $objectInstancier->FluxEntiteSQL->getUsedByConnecteur($connecteur['id_ce']);
    foreach ($flux_list as $flux) {
        $objectInstancier->FluxEntiteSQL->removeConnecteur($flux['id_fe']);
    }

    $objectInstancier->ConnecteurEntiteSQL->delete($connecteur['id_ce']);
}
echo "Les elements ont ete supprimés\n";
