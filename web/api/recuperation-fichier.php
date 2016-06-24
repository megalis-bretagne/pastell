<?php


/**
 * @api {get} /recuperation-fichier.php /Document/recuperationFichier
 * @apiDescription Récupère le contenu d'un fichier
 * @apiGroup Document
 * @apiVersion 1.0.0
 *
 * @apiParam {int} id_e requis Identifiant de l'entité (retourné par list-entite)
 * @apiParam {int} id_d Identifiant du document
 * @apiParam {string} field le nom du champs
 * @apiParam {string} num numéro du fichier (pour les fichier multiple)
 *
 * @apiSuccess {raw} raw_data le contenu du fihcier
 *
 */
//FIXME A mettre dans un APIController
require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
$id_d = $recuperateur->get('id_d');
$id_e = $recuperateur->get('id_e');
$field = $recuperateur->get('field');
$num = $recuperateur->getInt('num');

$document = $objectInstancier->Document;
$info = $document->getInfo($id_d);

if ( ! $roleUtilisateur->hasDroit($id_u,"{$info['type']}:edition",$id_e)) {
	$JSONoutput->displayErrorAndExit("Acces interdit id_e=$id_e, type={$info['type']},id_u=$id_u");
}


$document = $objectInstancier->Document;
$info = $document->getInfo($id_d);


$donneesFormulaire = $donneesFormulaireFactory->get($id_d,$info['type']);

$file_path = $donneesFormulaire->getFilePath($field,$num);
$file_name_array = $donneesFormulaire->get($field);
$file_name= $file_name_array[$num];

if (! file_exists($file_path)){
	$JSONoutput->displayErrorAndExit("Ce fichier n'existe pas");
	
}

$utilisateur = new Utilisateur($sqlQuery);
$infoUtilisateur = $utilisateur->getInfo($authentification->getId());
$nom = $infoUtilisateur['prenom']." ".$infoUtilisateur['nom'];

$journal->add(Journal::DOCUMENT_CONSULTATION,$id_e,$id_d,"Consulté","$nom a consulté le document $file_name");



header("Content-type: ".mime_content_type($file_path));
header("Content-disposition: attachment; filename=\"$file_name\"");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");

readfile($file_path);