#! /usr/bin/php
<?php

//Script à passer tous les jours pour éviter des problèmes de performance et de corruption de la table worker

require_once( dirname(__FILE__) . "/../init.php");

/** @var SQLQuery $sqlQuery */
$sqlQuery = $objectInstancier->getInstance('SQLQuery');

$list = array(
    "OPTIMIZE TABLE worker",
    "ANALYZE TABLE worker",
    "OPTIMIZE TABLE job_queue",
    "ANALYZE TABLE job_queue"
);
foreach($list as $command) {
    echo "$command:...";
    $sqlQuery->query($command);
    echo "[OK]\n";
}
