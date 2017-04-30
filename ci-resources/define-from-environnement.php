<?php

//Utilisé dans le cadre de PHPStorm qui ne permet pas de lancer des scripts après le démarrage du Docker
//et qui ne lance pas l'entrypoint
//Uniquement utilisé pour PHPUnit et Codeception donc

if (! file_exists(__DIR__."/../LocalSettings.php")) {
	$script = __DIR__ . "/docker-pastell-init";

	`/bin/bash $script > /tmp/DockerSettings.php`;

	require_once "/tmp/DockerSettings.php";
} else {
	require_once __DIR__."/../LocalSettings.php";
}
