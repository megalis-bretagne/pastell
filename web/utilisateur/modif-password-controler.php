<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");
$recuperateur = new Recuperateur($_POST);

$oldpassword = $recuperateur->get('old_password');
$password = $recuperateur->get('password');
$password2 = $recuperateur->get('password2');

if ($password != $password2){
	$objectInstancier->LastError->setLastError("Les mots de passe ne correspondent pas");
	header("Location: modifPassword");
	exit;
}

$utilisateur = new Utilisateur($sqlQuery);

if ( ! $utilisateur->verifPassword($authentification->getId(),$oldpassword)){
	$objectInstancier->LastError->setLastError("Votre ancien mot de passe est incorrecte");
	header("Location: modifPassword");
	exit;
}


$utilisateur->setPassword($authentification->getId(),$password);


$objectInstancier->LastMessage->setLastMessage("Votre mot de passe a été modifié");
header("Location: moi");