<?php

require_once(__DIR__ . "/../init-no-db.php");

$sqlQuery = new SQLQuery(BD_DSN, BD_USER, BD_PASS);
$sqlQuery->waitStarting(function ($m) {
    echo $m . "\n";
});
