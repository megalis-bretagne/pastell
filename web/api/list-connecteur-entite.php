<?php

require_once("init-api.php");
$recuperateur = new Recuperateur($_REQUEST);
// R�cup�ration des param�tres de la requ�te.    
$id_e = $recuperateur->getInt('id_e');

$api_json->listConnecteurEntite($id_e);

?>