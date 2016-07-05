<?php
require_once(dirname(__FILE__)."/../init.php");


$recuperateur = new Recuperateur($_POST);

$mail_verif_password = $recuperateur->get('mail_verif_password');
$password = $recuperateur->get('password');
$password2 = $recuperateur->get('password2');


$utilisateurListe = new UtilisateurListe($sqlQuery);
$id_u = $utilisateurListe->getByVerifPassword($mail_verif_password);

if ( ! $id_u ){
	$objectInstancier->LastError->setLastError("Utilisateur inconnu");
	header("Location: connexion");
	exit;
}

if (! $password){
	$objectInstancier->LastError->setLastError("Le mot de passe est obligatoire");
	header("Location: changementMdp?mail_verif=$mail_verif_password");
	exit;
}
if ($password != $password2){
	$objectInstancier->LastError->setLastError("Les mots de passe ne correspondent pas");
	header("Location: changementMdp?mail_verif=$mail_verif_password");
	exit;
}


$utilisateur = new Utilisateur($sqlQuery);
$infoUtilisateur = $utilisateur->getInfo($id_u);
$utilisateur->setPassword($id_u,$password);

$passwordGenerator = new PasswordGenerator();
$mailVerifPassword = $passwordGenerator->getPassword();
$utilisateur->reinitPassword($id_u,$mailVerifPassword);

$journal->add(Journal::MODIFICATION_UTILISATEUR,$infoUtilisateur['id_e'],0,"mot de passe modifié","{$infoUtilisateur['login']} ({$infoUtilisateur['id_u']}) a modifié son mot de passe");
$objectInstancier->LastMessage->setLastMessage("Votre mot de passe a été modifié");

header("Location: connexion");