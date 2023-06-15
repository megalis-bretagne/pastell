<?php

declare(strict_types=1);

use Pastell\Mailer\Mailer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

require_once __DIR__ . '/../init.php';

/** @deprecated Since 4.0.4, Use app:daemon:notify-check command instead */

trigger_error('Since 4.0.4, Use app:daemon:notify-check', E_USER_DEPRECATED);

$objectInstancier = ObjectInstancierFactory::getObjetInstancier();

$modenagios = false;
$message = "OK";
$retour = 0;

#print_r($argv);
if ((isset($argv[1])) && ($argv[1] === 'nagios')) {
    $modenagios = true;
}

$next_try = date("Y-m-d H:i:s", strtotime("-1hour"));

$sql = "SELECT MAX(last_try) FROM job_queue WHERE next_try<? AND nb_try > 0";

$last_try = $objectInstancier->getInstance(SQLQuery::class)->queryOne($sql, $next_try);

if (!$last_try) {
    //la job queue est vide
    if ($modenagios) {
        echo 'OK la job queue est vide';
    }

    exit(0);
}

$nb_second_since_last_try = time() - strtotime($last_try);

$pastellMailer = $objectInstancier->getInstance(Mailer::class);
$siteBase = $objectInstancier->getInstance('site_base');
$tos = $objectInstancier->getInstance('admin_email');

if ($nb_second_since_last_try > 3600) {
    $message = sprintf(
        "Le démon sur le site %s/Daemon semble arrêté depuis plus d'une heure",
        $siteBase
    );
    $templatedEmail = (new TemplatedEmail())
        ->to(...$tos)
        ->subject('[PASTELL] Le démon semble arrêté')
        ->text($message);
    $pastellMailer->send($templatedEmail);
    $retour = 2;
}


$jobQueueSQL = $objectInstancier->getInstance(JobQueueSQL::class);
$nb_lock = $jobQueueSQL->getNbLockSinceOneHour();
if ($nb_lock) {
    $message = sprintf(
        "%s connecteur(s) Pastell semble suspendus sur le site %s depuis plus d'une heure !",
        $nb_lock,
        $siteBase
    );
    $templatedEmail = (new TemplatedEmail())
        ->to(...$tos)
        ->subject('[PASTELL] Des connecteurs sont suspendus')
        ->text($message);
    $pastellMailer->send($templatedEmail);
    $retour = 2;
}

if ($modenagios) {
    echo $message;
    exit($retour);
}
