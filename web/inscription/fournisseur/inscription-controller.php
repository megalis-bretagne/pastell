<?php
include( dirname(__FILE__) . "/../../init.php");
require_once( PASTELL_PATH . "/lib/Siren.class.php");
require_once( PASTELL_PATH . "/lib/Redirection.class.php");
require_once( PASTELL_PATH . "/lib/MailVerification.class.php");

$redirection = new Redirection("index.php");

$recuperateur = new Recuperateur($_POST);
$email = $recuperateur->get('email');
$siren = $recuperateur->get('siren');
$login = $recuperateur->get('login');
$password = $recuperateur->get('password');
$password2 = $recuperateur->get('password2');
$nom = $recuperateur->get('nom');
$prenom = $recuperateur->get('prenom');
$denomination = $recuperateur->get('denomination');


$entite = new Entite($sqlQuery,$siren);
if ($entite->exists()){
	$objectInstancier->LastError->setLastError("Le siren que vous avez déjà indiqué est déjà connu sur la plateforme");
	$redirection->redirect();
}

$sirenVerifier = new Siren();
if (! $sirenVerifier->isValid($siren)){
	$objectInstancier->LastError->setLastError("Votre siren ne semble pas valide");
	$redirection->redirect();
}

if ( ! $denomination ){
	$objectInstancier->LastError->setLastError("Il faut saisir une raison sociale");
	$redirection->redirect();
}

$id_u = $objectInstancier->UtilisateurCreator->create($login,$password,$password2,$email);

if ( ! $id_u){
	$objectInstancier->LastError->setLastError($objectInstancier->UtilisateurCreator->getLastError());
	$redirection->redirect();
}

$utilisateur = new Utilisateur($sqlQuery);
$utilisateur->setNomPrenom($id_u,$nom,$prenom);

$entiteCreator = new EntiteCreator($sqlQuery,$journal);
$id_e = $entiteCreator->edit(false,$siren,$denomination,Entite::TYPE_FOURNISSEUR,0,0);

$roleUtilisateur->addRole($id_u,"fournisseur",$id_e);

$infoUtilisateur = $utilisateur->getInfo($id_u);

$zMail = $objectInstancier->ZenMail;
$mailVerification = new MailVerification($zMail);
$mailVerification->send($infoUtilisateur);

$redirection->redirect("inscription-ok.php");