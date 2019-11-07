<?php

/**
 * Exemple d'utilisation de l'API
 * Exemple avec version.php
 */

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_URL, "http://localhost:8888/pastell/api/version.php");
curl_setopt($curl, CURLOPT_USERPWD, "admin:admin");

$output = curl_exec($curl);

if ($err = curl_error($curl)) {
    echo "Error : " . $err;
    exit;
}

$result  = json_decode($output, true);

echo $result['version'] . "\n";
