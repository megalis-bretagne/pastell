<?php
require_once(dirname(__FILE__)."/../init-authenticated.php");


$recuperateur = new Recuperateur($_GET);
$id_d = $recuperateur->get('id_d');
$id_e = $recuperateur->get('id_e');
$field = $recuperateur->get('field');
$num = $recuperateur->getInt('num');


$document = $objectInstancier->Document;
$info = $document->getInfo($id_d);


$donneesFormulaire = $donneesFormulaireFactory->get($id_d,$info['type']);


$file_path = $donneesFormulaire->getFilePath($field,$num);
$file_name_array = $donneesFormulaire->get($field);
$file_name= $file_name_array[$num];

if (! file_exists($file_path)){
	$objectInstancier->LastError->setLastError("Ce fichier n'existe pas");
	header("Location: index");
	exit;
}

$utilisateur = new Utilisateur($sqlQuery);
$infoUtilisateur = $utilisateur->getInfo($authentification->getId());
$nom = $infoUtilisateur['prenom']." ".$infoUtilisateur['nom'];

$journal->add(Journal::DOCUMENT_CONSULTATION,$id_e,$id_d,"Consulté","$nom a consulté le document $file_name");

if (mb_strlen($file_name) > 80){
	$pos = mb_strrpos($file_name,".");
	$name = mb_substr($file_name,0,$pos);
	$extension = mb_substr($file_name,$pos + 1 ,mb_strlen($file_name));
	$file_name = mb_substr($name,0,76).".".$extension;
}

header("Content-type: ".mime_content_type($file_path));
header("Content-disposition: attachment; filename=\"".urlencode($file_name)."\"");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");

readfile($file_path);