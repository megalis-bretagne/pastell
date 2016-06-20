<?php
require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
// Récupération des paramètres de la requête. 
$data = $recuperateur->getAll();

$api_json->modifEntite($data);

?>