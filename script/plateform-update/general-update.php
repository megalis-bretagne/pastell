<?php

/**
 * Ce script doit être appelé à chaque mise à jour
 * Il procède à toutes les opérations de mise à jour, sauf la base de données
 */
require_once __DIR__."/../../init.php";


$scriptname = basename($argv[0]??'general-update');

$pastellLogger = $objectInstancier->getInstance(PastellLogger::class);
$pastellLogger->setName($scriptname);
$pastellLogger->enableStdOut(true);

$pastellLogger->info("Démarrage du script");

$pastellUpdater = $objectInstancier->getInstance(PastellUpdater::class);
$pastellUpdater->update();

$pastellBootstap = $objectInstancier->getInstance(PastellBootstrap::class);

try {
	$pastellBootstap->rebuildTypeDossierPersonnalise();
} catch (Exception $e){
	$pastellLogger->error("Impossible de reconstruire les type de dossier, arrêt du script");
	$pastellLogger->error($e->getMessage());
	$pastellLogger->error($e->getTraceAsString());
	exit(-1);

}

$pastellBootstap->flushRedis();

$pastellLogger->info("Fin du script");

exit(0);