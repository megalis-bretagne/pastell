<?php

require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
$id_extension = $recuperateur->getInt('id_extension');
$path = $recuperateur->get('path');

$api_json->editExtension($id_extension,$path);
