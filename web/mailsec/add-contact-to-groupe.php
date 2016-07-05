<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");

$recuperateur = new Recuperateur($_POST);
$id_e = $recuperateur->getInt('id_e');
$name = $recuperateur->get('name');
$id_g = $recuperateur->get('id_g');

if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) {
	header("Location: annuaire?id_e=$id_e");
	exit;
}

preg_match("/<([^>]*)>/",$name,$matches);
$email = $matches[1];

$annuaire = new AnnuaireSQL($sqlQuery);
$id_a = $annuaire->getFromEmail($id_e,$email);

if (! $id_a){
	$objectInstancier->LastError->setLastError("L'email $email est inconnu");
	header("Location: groupe?id_e=$id_e&id_g=$id_g");
	exit;
}

$annuaireGroupe = new AnnuaireGroupe($sqlQuery,$id_e);
$annuaireGroupe->addToGroupe($id_g,$id_a);

$mail = htmlentities($name,ENT_QUOTES);

$objectInstancier->LastMessage->setLastMessage("$mail a été ajouté à ce groupe");
header("Location: groupe?id_e=$id_e&id_g=$id_g");