<?php

require_once(__DIR__ . "/../init.php");
$modenagios = false;
$message = "OK";
$retour = 0;

#print_r($argv);
if ((isset($argv[1])) && ($argv[1] == 'nagios')) {
        $modenagios = true;
}

$next_try = date("Y-m-d H:i:s", strtotime("-1hour"));

$sql = "SELECT MAX(last_try) FROM job_queue WHERE next_try<? AND nb_try > 0";

$last_try = $sqlQuery->queryOne($sql, $next_try);

if (! $last_try) {
        //la job queue est vide
    if ($modenagios) {
        echo "OK la job queue est vide";
    }

        exit(0);
}

$nb_second_since_last_try = time() - strtotime($last_try);

if ($nb_second_since_last_try > 3600) {
        mail_wrapper(
            ADMIN_EMAIL,
            "Le démon Pastell semble arreté",
            "Le démon sur le site " . SITE_BASE . "/Daemon semble arreté depuis plus d'une heure"
        );
        $message = "Le démon Pastell semble arreté sur le site " . SITE_BASE . "/Daemon semble arreté depuis plus d'une heure";
        $retour = 2;
}


/** @var JobQueueSQL $jobQueueSQL */
$jobQueueSQL = $objectInstancier->getInstance(JobQueueSQL::class);
$nb_lock = $jobQueueSQL->getNbLockSinceOneHour();
if ($nb_lock) {
        mail_wrapper(
            ADMIN_EMAIL,
            "Des connecteurs Pastell sont suspendus",
            "$nb_lock connecteur(s) Pastell semble suspendus sur le site " . SITE_BASE . " depuis plus d'une heure !"
        );
        $message = "Des connecteurs Pastell, " . SITE_BASE . ", sont suspendus depuis plus d'une heure !";
        $retour = 2;
}

if ($modenagios) {
        echo $message;
        exit($retour);
}
