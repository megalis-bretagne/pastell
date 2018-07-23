<?php

require_once __DIR__ . "/../../../init.php";

//Renseigne le champs document_entite:last_type nécessaire au calcul rapide du nombre de document par entité et type

$documentEntite = $objectInstancier->getInstance(DocumentEntite::class);
$documentEntite->fixLastType();


