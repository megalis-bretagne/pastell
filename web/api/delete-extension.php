<?php

require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
$id_extension = $recuperateur->getInt('id_extension');

$api_json->deleteExtension($id_extension);
