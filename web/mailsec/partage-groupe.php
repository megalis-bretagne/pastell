<?php 
require_once(dirname(__FILE__)."/../init-authenticated.php");

$recuperateur = new Recuperateur($_POST);
$id_e = $recuperateur->getInt('id_e');
$id_g = $recuperateur->get('id_g');

if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) {
	header("Location: annuaire?id_e=$id_e");
	exit;
}

$annuaireGroupe = new AnnuaireGroupe($sqlQuery,$id_e);
$annuaireGroupe->tooglePartage($id_g);
$info = $annuaireGroupe->getInfo($id_g);
if ($info['partage']){
	$objectInstancier->LastMessage->setLastMessage("Le groupe est maintenant partagé");
} else {
	$objectInstancier->LastMessage->setLastMessage("Le partage du groupe a été supprimé");
}
header("Location: groupe?id_e=$id_e&id_g=$id_g");