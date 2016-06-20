<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");

$recuperateur = new Recuperateur($_POST);
$id_e = $recuperateur->getInt('id_e');
$description = $recuperateur->get('description',"");
$email = $recuperateur->get('email');


if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) {
	header("Location: annuaire.php?id_e=$id_e");
	exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
	$objectInstancier->LastError->setLastError("$email ne semble pas être un email valide");
	header("Location: annuaire.php?id_e=$id_e");
	exit;
}

$annuaire = new AnnuaireSQL($sqlQuery);

if($annuaire->getFromEmail($id_e,$email)){
	$objectInstancier->LastError->setLastError("$email existe déjà dans l'annuaire");
	header("Location: annuaire.php?id_e=$id_e");
	exit;	
}

$annuaire->add($id_e,$description,$email);

$mail = htmlentities("\"$description\"<$email>",ENT_QUOTES);

$objectInstancier->LastMessage->setLastMessage("$mail a été ajouté à la liste de contacts");
header("Location: annuaire.php?id_e=$id_e");