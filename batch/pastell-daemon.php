<?php 
require_once( __DIR__ . "/../web/init.php");

$daemonManager = $objectInstancier->DaemonManager;

$arg = get_argv(1);

if (! in_array($arg,array('start','stop','restart','status'))){	
	echo "{$argv[0]} gestion du démarrage et de l'arrêt du script de lancement des workers\n";
	echo "Usage : {$argv[0]} {start|stop|restart|status}\n";
	exit;
}

if ($arg == 'start'){
	$daemonManager->start();
}

if ($arg == 'stop'){
	$daemonManager->stop();
}

if ($arg == 'restart'){
	$daemonManager->restart();
}

if ($daemonManager->status()==DaemonManager::IS_RUNNING){
	echo "Pastell job master is running\n";
} else {
	echo "Pastell job master is stopped\n";
}	


