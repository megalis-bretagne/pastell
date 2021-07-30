<?php

/**
 * @var ObjectInstancier $objectInstancier
 * @var SQLQuery $sqlQuery
 */

use Pastell\Service\Document\DocumentSize;

require_once __DIR__ . '/../../init.php';

$entiteSQL = $objectInstancier->getInstance(EntiteSQL::class);
$documentSize = $objectInstancier->getInstance(DocumentSize::class);
$sql = "SELECT id_d FROM document_entite WHERE id_e=?";

foreach ($entiteSQL->getAll() as $entite_info) {
    $id_d_list = $sqlQuery->queryOneCol($sql, $entite_info['id_e']);
    $size = 0;
    foreach ($id_d_list as $id_d) {
        $size += $documentSize->getSize($id_d);
    }

    $out = [
        $entite_info['id_e'],
        $entite_info['denomination'],
        $size,
        $documentSize->getHumanReadableSize($size),
    ];

    echo implode(';', $out) . PHP_EOL;
}
