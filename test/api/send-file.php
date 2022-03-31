<?php

$file_path = __DIR__ . "/vide.pdf";

$post_data = [
        'field' => 'fichier1',
        'file_name' => 'vide.pdf',
        'file_content' => file_get_contents($file_path),
        'id_d' => 'C65p6sz',
        'id_e' => '2',
];

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_URL, "https://192.168.1.11:8443/api/send-file.php");
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
