<?php

$file_path = __DIR__ . "/vide.pdf";

$post_data = [
        "field_name" => "fichier_pes",
        "file_name" => "vide.pdf",
        "file_content" => "@$file_path",
        'id_d' => 'q17fx3d',
        'id_e' => '1',
];

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_URL, "http://192.168.1.28:8888/adullact/pastell/web/api/modif-document.php");
curl_setopt($curl, CURLOPT_USERPWD, "admin:admin");
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($curl, CURLOPT_VERBOSE, 1);
echo "begin";

$output = curl_exec($curl);

if ($err = curl_error($curl)) {
    echo "Error : " . $err;
}

echo $output . "\n";
