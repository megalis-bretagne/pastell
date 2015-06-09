<?php

/*
$file = "/tmp/pastell-api.log";
$file_handle = fopen($file,"a+");


fwrite($file_handle, date("Y-m-d H:i:s")."Début de l'appel à l'API modif-document\n");

foreach($_REQUEST as $key => $value){
        fwrite($file_handle,"Request : $key => $value\n");
}

if (isset($_SERVER['SSL_CLIENT_CERT'])){
        fwrite($file_handle,"SSL_CLIENT_CERT : {$_SERVER['SSL_CLIENT_CERT']}\n");
}
foreach($_FILES as $id => $info){
        fwrite($file_handle,"FILE : $id : {$info['name']}\n");
        foreach($info as $key => $value){
                fwrite($file_handle,"FILE_key : $key : $value\n");
        }
}

fwrite($file_handle,"AUTH : {$_SERVER['PHP_AUTH_USER']}:{$_SERVER['PHP_AUTH_PW']}\n");
fwrite($file_handle,"\n");
fclose($file_handle);


*/

require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
$data = $recuperateur->getAll();

$fileUploader = new FileUploader();

$api_json->modifDocument($data,$fileUploader);
