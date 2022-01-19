#! /usr/bin/php
<?php

/**
 * TODO: Remove in 4.0
 */

$start = time();
$min_exec_time = 60;

require_once(__DIR__ . "/../init.php");

$objectInstancier->LastUpstart->updatePID();


$journal->horodateAll();


$objectInstancier->LastUpstart->updateMtime();

$stop = time();
$sleep = $min_exec_time - ($stop - $start);
if ($sleep > 0) {
    echo "Arret du script : $sleep";
    sleep($sleep);
}
$objectInstancier->LastUpstart->deletePID();
