<?php

/**
 * Ce script doit être appelé à chaque mise à jour
 * Il procède à toutes les opérations de mise à jour, sauf la base de données
 */
require_once __DIR__."/../../init.php";


$scriptname = basename($argv[0]);

$pastellLogger = $objectInstancier->getInstance(PastellLogger::class);
$pastellLogger->setName($scriptname);
$pastellLogger->enableStdOut(true);

$pastellLogger->info("Démarrage du script");


$typeDossierService = $objectInstancier->getInstance(TypeDossierService::class);
try {
	$typeDossierService->rebuildAll();
} catch (Exception $e){
	$pastellLogger->error("Impossible de reconstruire les type de dossier, arrêt du script");
	$pastellLogger->error($e->getMessage());
	$pastellLogger->error($e->getTraceAsString());
	exit(-1);

}

$pastellLogger->info("Vidage du cache");
$redisWrapper = $objectInstancier->getInstance(MemoryCache::class);
$redisWrapper->flushAll();
$pastellLogger->info("Le cache a été vidé");

$pastellLogger->info("Fin du script");
exit(0);