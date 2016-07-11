<?php

require_once(__DIR__."/../../init.php");


/* S2low doit appeller ce script afin que celui-ci dépose les fichiers OCRE dans le répertoire input */
/* Ce script doit être avec un POST */


if (empty($_POST['passphrase']) || $_POST['passphrase'] != OCRE_RECEIVE_PASSPHRASE){
	sortirKO("KO passphrase incorrecte");
}

if ($_FILES["ocre"]["error"] != UPLOAD_ERR_OK) {
	sortirKO("KO upload échoué - code {$_FILES["ocre"]["error"]}");

}

$tmp_name = $_FILES["ocre"]["tmp_name"];
$filename = $_FILES["ocre"]["name"];
if (file_exists( OCRE_INPUT_DIRECTORY."/$filename")){
	sortirKO("oops, le fichier existe déjà !");
}

if (move_uploaded_file($tmp_name, OCRE_INPUT_DIRECTORY."/$filename") === false){
	sortirKO("Impossible de déplacer le fichier");
}

echo json_encode(array("result"=>'OK',"message"=>"fichier récupéré"));


function sortirKO($message){
	echo json_encode(array("result"=>'KO',"message"=>$message));
	exit;
}

