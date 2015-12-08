<?php

require_once(__DIR__."/../../../../init.php");

$manifestFactory = new ManifestFactory(PASTELL_PATH,new YMLLoader(new APCWrapper()));

$info = $manifestFactory->getPastellManifest()->getInfo();

$result = array("produit"=>"Pastell","version"=>$info['version']);

print_r($result);