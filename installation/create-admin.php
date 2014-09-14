<?php
//Cr�e un admin (cr�e aussi le r�le admin et fixe les droits si il n'existe pas

require_once( __DIR__ . "/../web/init.php");


$login = get_argv(1);
$password = get_argv(2);
$email = get_argv(3);

$result = $objectInstancier->AdminControler->createAdmin($login,$password,$email);

if ($result){
		echo "Administrateur $login cr�� avec succ�s\n";
	
} else {
	echo $objectInstancier->AdminControler->getLastError()."\n";	
	echo "Usage : {$argv[0]} login password email\n";
	exit;
}

$objectInstancier->AdminControler->fixDroit();