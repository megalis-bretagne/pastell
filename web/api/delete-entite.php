<?php
require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
// R�cup�ration des param�tres de la requ�te. 
$id_e_a_supprimer = $recuperateur->get('id_e');

$api_json->deleteEntite($id_e_a_supprimer);

?>