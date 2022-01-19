<?php

/**
 * @var ObjectInstancier $objectInstancier
 * @var Monolog\Logger $logger
 */

require_once __DIR__ . '/../init.php';

$daemonManager = $objectInstancier->getInstance(DaemonManager::class);

$arg = get_argv(1);

if (! in_array($arg, array('start','stop','restart','status'))) {
    echo "{$argv[0]} gestion du démarrage et de l'arrêt du script de lancement des workers\n";
    echo "Usage : {$argv[0]} {start|stop|restart|status}\n";
    exit;
}

$logger->addInfo("Daemon <<$arg>> command called");

if ($arg == 'start') {
    $daemonManager->start();
}

if ($arg == 'stop') {
    $daemonManager->stop();
}

if ($arg == 'restart') {
    $daemonManager->restart();
}

$logger->addInfo("Daemon status after $arg command called : " . $daemonManager->status());

if ($daemonManager->status() == DaemonManager::IS_RUNNING) {
    echo "Pastell job master is running\n";

    if ($arg == 'stop') {
        $logger->addCritical("Fail to send command $arg to daemon");
        exit(-1);
    }
    exit(0);
} else {
    echo "Pastell job master is stopped\n";
    if (! in_array($arg, ['stop','status'])) {
        $logger->addCritical("Fail to start command $arg to daemon");
        exit(-1);
    }
    exit(0);
}
