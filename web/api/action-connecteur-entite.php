<?php

require_once("init-api.php");
$recuperateur = new Recuperateur($_REQUEST);
// R�cup�ration des param�tres de la requ�te.    
$id_e = $recuperateur->getInt('id_e');
$type_connecteur = $recuperateur->get('type');
$flux = $recuperateur->get('flux');
$action = $recuperateur->get('action');
$action_params = $recuperateur->get('action_params');
if (!$action_params) {
    $action_params=array();
}

$api_json->actionConnecteurEntite($id_e, $type_connecteur, $flux, $action, $action_params);
?>
