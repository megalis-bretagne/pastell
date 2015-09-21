<?php
require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
$data = $recuperateur->getAll();

$api_json->addSeveralRolesUtilisateur($data);

?>