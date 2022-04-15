<?php

//TODO  à supprimer - eventuellement mettre l'état dans ForceUpdateField (et faire 2 commande, une pour connecteur et une pour document)

/**
 * @var ObjectInstancier $objectInstancier
 */
require_once __DIR__ . '/../init.php';


if (count($argv) != 6) {
    echo "{$argv[0]} : Modifie un ensemble de document\n";
    echo "Usage : {$argv[0]} id_e type_document etat field_name field_value\n";
    exit;
}

$id_e = get_argv(1);
$type = get_argv(2);
$etat = get_argv(3);
$field_name = get_argv(4);
$field_value = get_argv(5);

try {
    $nb_doc = $objectInstancier
        ->getInstance(DocumentControler::class)
        ->bulkModification($id_e, $type, $etat, $field_name, $field_value);
    echo "$nb_doc documents ont été modifiés\n";
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}
