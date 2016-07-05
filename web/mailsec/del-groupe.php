<?php 
require_once(dirname(__FILE__)."/../init-authenticated.php");

$recuperateur = new Recuperateur($_POST);
$id_e = $recuperateur->getInt('id_e');
$id_g = $recuperateur->get('id_g',array());

if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) {
	header("Location: annuaire?id_e=$id_e");
	exit;
}

$annuaireGroupe = new AnnuaireGroupe($sqlQuery,$id_e);

$annuaireGroupe->delete($id_g);

if ($id_g) {
	$objectInstancier->LastMessage->setLastMessage("Les groupes sélectionnés ont été supprimés");
}
header("Location: groupeList?id_e=$id_e");