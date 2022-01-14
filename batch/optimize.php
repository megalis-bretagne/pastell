#! /usr/bin/php
<?php

//Script à passer tous les jours pour éviter des problèmes de performance et de corruption de la table worker

require_once __DIR__ . "/../init.php";

/** @var SQLQuery $sqlQuery */
$sqlQuery = $objectInstancier->getInstance(SQLQuery::class);

$list = array(
    "OPTIMIZE TABLE worker",
    "OPTIMIZE TABLE job_queue",
    "OPTIMIZE TABLE journal",
);
foreach ($list as $command) {
    echo "$command:...";
    $sqlQuery->query($command);
    echo "[OK]\n";
}
