<?php

require_once( __DIR__ . "/../init-no-db.php");

$connected = false;

$nb_retry = 0;
do {
	try {
		$nb_retry ++;
		$sqlQuery = new SQLQuery(BD_DSN, BD_USER, BD_PASS);
		$sqlQuery->query("SELECT 1;");
		echo "Mysql is started now\n";
		$connected = true;
	} catch (Exception $e) {
		echo "Mysql not started [$nb_retry]... wait one second more\n";
		sleep(1);
	}
} while(! $connected && $nb_retry < 60);


if ($connected){
	exit(0);
} else {
	echo "Mysql not started after $nb_retry... exiting\n";
	exit(1);
}