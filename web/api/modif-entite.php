<?php
require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
// R�cup�ration des param�tres de la requ�te. 
$data = $recuperateur->getAll();

$api_json->modifEntite($data);

?>