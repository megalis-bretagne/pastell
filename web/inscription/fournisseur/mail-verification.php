<?php 
require_once( dirname(__FILE__) . "/../../init.php");


$recuperateur = new Recuperateur($_GET);

$password = $recuperateur->get('password');
$login = $recuperateur->get('login');

$utilisateurListe = new UtilisateurListe($sqlQuery);
$id_u = $utilisateurListe->getUtilisateurByLogin($login);

$utilisateur = new Utilisateur($sqlQuery);
$result = $utilisateur->validMail($id_u,$password);

if ($result){
	$objectInstancier->LastMessage->setLastMessage("Votre mail est maintenant validé");
} else {
	$objectInstancier->LastError->setLastError("Le mail n'a pas pu être validé");
}

header("Location: " .SITE_BASE. "connexion/connexion.php");
