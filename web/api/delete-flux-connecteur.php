<?php
require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
// R�cup�ration des param�tres de la requ�te. 
$id_e = $recuperateur->getInt('id_e');
$id_fe = $recuperateur->getInt('id_fe');

$api_json->deleteFluxConnecteur($id_e, $id_fe);

?>