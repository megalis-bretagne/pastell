<?php
//TODO à supprimer

require_once __DIR__ . "/../../init.php";

$sql = "SELECT * FROM type_dossier";

$result = $sqlQuery->query($sql);


foreach ($result as $line) {
    $definition = json_decode($line['definition'], true);
    $modif = false;
    foreach ($definition['etape'] as $i => $etape) {
        if ($etape['type'] == 'tdt-actes') {
            $definition['etape'][$i]['type'] = 'tdt_actes';
            $modif = true;
        }
        if ($etape['type'] == 'tdt-helios') {
            $definition['etape'][$i]['type'] = 'tdt_helios';
            $modif = true;
        }
    }
    if ($modif) {
        print_r($definition);
        $sqlQuery->query("UPDATE type_dossier SET definition=? WHERE id_t=?", json_encode($definition), $line['id_t']);
    }
}
