<?php

//Ce script permet d'ajouter un champs supplémentaire.
//Exemple : lors d'une évolution de helios-generique, un champs a été ajouté (envoi_signature_check)
// Celui-ci ne doit être ajouté que pour les document helios-generique qui ont un chmaps envoi_signature à true

/**
 * TODO: Remove in 4.0
 */

require_once(__DIR__ . "/../../init.php");

$do_things = false;

$flux_name = 'helios-generique';

$regexp_condition_true = "#^envoi_signature: (.*)$#m";
$regexp_condition_false = "#envoi_signature_check:#m";

$new_field = "envoi_signature_check: ";


$result = $objectInstancier->Document->getAllByType($flux_name);

if (!$result) {
    echo "Il n'y a pas de document de type $flux_name\n";
    exit;
}

if (! $do_things) {
    echo "Les documents suivants seraient modifiés : \n";
}

foreach ($result as $document_info) {
    $file_path = $objectInstancier->DonneesFormulaireFactory->getNewDirectoryPath($document_info['id_d']) . "{$document_info['id_d']}.yml";
    if (! file_exists($file_path)) {
        continue;
    }
    $file_content = file_get_contents($file_path);

    if (! preg_match($regexp_condition_true, $file_content, $matches)) {
        continue;
    }
    if (preg_match($regexp_condition_false, $file_content)) {
        continue;
    }

    $field_value = $matches[1];

    $file_content .= $new_field . $field_value . "\n";
    if ($do_things) {
        file_put_contents($file_path, $file_content);
    }
    echo $document_info['id_d'] . " : OK \n";
}

if ($do_things) {
    echo "Les documents ont ete modifies\n";
}
