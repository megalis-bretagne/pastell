<?php

require_once( __DIR__ . "/../web/init.php");



$sql = "SELECT MAX(last_try) FROM job_queue";

$last_try = $sqlQuery->queryOne($sql);

if (! $last_try){
	//la job queue est vide
	exit(0);
}

$nb_second_since_last_try = time() - strtotime($last_try);

if ($nb_second_since_last_try > 3600){
	
	mail(
		ADMIN_EMAIL,
		"Le d�mon Pastell semble arret�","Le d�mon sur le site ".SITE_BASE."/daemon semble arret� depuis plus d'une heure"
	);
	
}

