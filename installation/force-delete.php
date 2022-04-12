#! /usr/bin/php
<?php

/**
 * @var ObjectInstancier $objectInstancier
 */

require_once __DIR__ . '/../init.php';

$id_d = get_argv(1);

if (!$id_d) {
    echo "Usage : {$argv[0]} id_d\n";
    exit;
}

$info = $objectInstancier->getInstance(DocumentSQL::class)->getInfo($id_d);
$objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($id_d)->delete();
$objectInstancier->getInstance(DocumentSQL::class)->delete($id_d);

$message = "Le document « {$info['titre']} » ($id_d) a été supprimé par un administrateur";
$objectInstancier->getInstance(Journal::class)->add(Journal::DOCUMENT_ACTION, 0, $id_d, "suppression", $message);

echo "Le document $id_d a été supprimé\n";
