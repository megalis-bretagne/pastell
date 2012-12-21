<?php
//Cr�e un admin (cr�e aussi le role admin et fixe les droits si il n'existe pas

require_once( dirname(__FILE__) . "/../web/init.php");

$get_argv = function ($num_arg) use ($argv){
	if (empty($argv[$num_arg])){
		return false;
	}
	return $argv[$num_arg];
};

$login = $get_argv(1);
$password = $get_argv(2);
$email = $get_argv(3);

$result = $objectInstancier->AdminControler->createAdmin($login,$password,$email);

if ($result){
		echo "Administrateur $login cr�e avec succ�s\n";
	
} else {
	echo $objectInstancier->AdminControler->getLastError()."\n";	
	echo "Usage : {$argv[0]} login password email\n";
	exit;
}

$objectInstancier->AdminControler->fixDroit();