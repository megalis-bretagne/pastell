<?php


//$script = __DIR__ . "/../../ci-resources/docker-pastell-init";
//`/bin/bash $script`;


//readfile("/etc/pastell/DockerSettings.php");


require_once __DIR__."/../../../ci-resources/define-from-environnement.php";

require_once __DIR__."/../../../init.php";

$result = $sqlQuery->query("DELETE FROM utilisateur");

$result = $objectInstancier->AdminControler->createAdmin('admin', 'admin', 'eric.pommateau@libriciel.coop');
