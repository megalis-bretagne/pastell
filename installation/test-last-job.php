<?php

require_once( __DIR__ . "/../init.php");
$modenagios=false;
$message="OK";
$retour=0;

#print_r($argv);
if( (isset($argv[1])) && ($argv[1] == 'nagios')) {
        $modenagios=true;
}

$sql = " SELECT MAX(last_try) FROM job_queue WHERE next_try<now() AND nb_try > 0";

$last_try = $sqlQuery->queryOne($sql);

if (! $last_try){
        //la jiob queue est vide
        $message="OK la job queue est vide";
}

$nb_second_since_last_try = time() - strtotime($last_try);

if ($nb_second_since_last_try > 3600){
        mail(
                ADMIN_EMAIL,
                "Le démon Pastell semble arreté","Le démon sur le site ".SITE_BASE."/daemon semble arreté depuis plus d'une heure"
        );
        $message="Le démon Pastell semble arreté sur le site ".SITE_BASE."/daemon semble arreté depuis plus d'une heure";
        $retour=2;
}


/** @var JobQueueSQL $jobQueueSQL */
$jobQueueSQL = $objectInstancier->getInstance("JobQueueSQL");
$nb_lock = $jobQueueSQL->getNbLockSinceOneHour();
if ($nb_lock){
        mail(
                ADMIN_EMAIL,
                "Des connecteurs Pastell sont verrouillés",
                "$nb_lock connecteur(s) Pastell semble verrouillés sur le site ".SITE_BASE." depuis plus d'une heure !"
        );
        $message="Des connecteurs Pastell, ".SITE_BASE.", sont verrouillés depuis plus d'une heure !";
        $retour=2;
}

if($modenagios){
        echo $message;
        exit($retour);
}
