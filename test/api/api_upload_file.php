<?php

/*
$post_data = array(
        "type"=>"actes-generique",
        'id_e'=>'1',
);
$url="https://pastell.devlocal.org/api/create-document.php";

$retour=pastell_api($url, $post_data);
$id_d=$retour["id_d"];
//var_dump($retour);



//$file_path = __DIR__."/vide.pdf";

$post_data = array(
 //"fichier_pes"=>"@$file_path",
 'acte_nature' => '1',
 "objet" => "testapi",
 "numero_de_lacte" => "TESTAPI",
 'id_d'=>$id_d,
 //"date_de_l_acte"=>"2015-07-29",
 //'classification'=>'4.5 Regime indemnitaire',
    "envoi_signature"=>"on",
    'envoi_tdt'=>'1',
 //'envoi_tdt'=>'on',
 //'action' => 'send-iparapheur',
    'id_e'=>'1',
 );

 $url="https://pastell.devlocal.org/api/modif-document.php";

*/
$id_d = 'MgaY0ER';

$post_data = [

    'id_d' => $id_d,
    'action' => 'teletransmission-tdt',
    'id_e' => '1',
];
 $url = "https://pastell.devlocal.org/api/Document/action";

 $retour = pastell_api($url, $post_data);
 var_dump($retour);



function pastell_api($url, $post_data)
{

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERPWD, "admin:admin");

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);

    $output = curl_exec($curl);
    //var_dump($output);
    if ($err = curl_error($curl)) {
        echo "Error : " . $err;
    }
    $retour = json_decode($output, true);
    return $retour;
}
