<?php
require_once( dirname(__FILE__) . "/../init-authenticated.php");

$recuperateur = new Recuperateur($_POST);

if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"entite:edition",0) ) {
	header("Location: " . SITE_BASE ."index.php");
	exit;
}
$fileUploader = new FileUploader();
$file_path = $fileUploader->getFilePath('csv_grade');
if (! $file_path){
	$objectInstancier->LastError->setLastError("Impossible de lire le fichier : " . $fileUploader->getLastError());
	header("Location: import?page=1");
	exit;	
}

$CSV = new CSV();
$gradeSQL = new GradeSQL($sqlQuery);
$gradeSQL->clean();

$fileContent = $CSV->get($file_path);

$nb_grade = 0;
foreach($fileContent as $info){
	if (count($info) != 6){
		continue;
	}
	$gradeSQL->add($info);
	$nb_grade++;
}


$objectInstancier->LastMessage->setLastMessage("$nb_grade grades ont été importés");
header("Location: import?page=2");
