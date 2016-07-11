<?php

require_once( __DIR__ . "/../init.php");



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
		"Le démon Pastell semble arreté","Le démon sur le site ".SITE_BASE."/daemon semble arreté depuis plus d'une heure"
	);
	
}
