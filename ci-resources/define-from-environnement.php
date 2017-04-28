<?php

if (! file_exists(__DIR__."/../LocalSettings.php")) {
	$script = __DIR__ . "/docker-pastell-init";

	`/bin/bash $script > /tmp/DockerSettings.php`;

	require_once "/tmp/DockerSettings.php";
}
