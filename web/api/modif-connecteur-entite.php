<?php

require_once("init-api.php");
$recuperateur = new Recuperateur($_REQUEST);
// R�cup�ration des param�tres de la requ�te.    
$id_e = $recuperateur->getInt('id_e');
$id_ce = $recuperateur->getInt('id_ce');
$libelle = $recuperateur->get('libelle');

$api_json->modifConnecteurEntite($id_e, $id_ce, $libelle);

?>