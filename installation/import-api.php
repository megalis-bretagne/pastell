<?php
// TODO à supprimer

require_once(__DIR__ . "/../init.php");


if ($argc < 5) {
    echo "Usage : {$argv[0]} PASTELL_API_URL LOGIN PASSWORD YML_FILE\n";
    echo "Permet d'appeller l'API Pastell en lui passant un fichier YML contenant les noms des fonctions comme clé : \n";
    echo " - paramètre input : les paramètres passé en POST";
    echo " - paramètre output : possibilité de récupérer des paramètres pour les call suivants";

    exit;
}

$url = $argv[1];
$login = $argv[2];
$password = $argv[3];
$yml_file = $argv[4];

$ymlLoader = new YMLLoader(new MemoryCacheNone());

$data = $ymlLoader->getArray($yml_file);

$global_data = [];

foreach ($data as $api_call => $post_data) {
    if (isset($post_data['input'])) {
        $input = $post_data['input'];
    } else {
        $input = [];
    }

    foreach ($input as $key => $value) {
        if ($value[0] == '+') {
            $input[$key] = $global_data[$value];
        }
    }


    $curlWrapper = new CurlWrapper();
    $curlWrapper->httpAuthentication($login, $password);


    foreach ($input as $key => $value) {
        $curlWrapper->addPostData($key, $value);
    }
    $call_url = $url . "/{$api_call}.php";

    echo "[CALL $api_call] : $call_url\nWith POST data : \n";
    print_r($input);

    $output_data = $curlWrapper->get($call_url);

    echo "Réponse : {$output_data}\n";

    if (! $output_data) {
        echo "ERREUR : " . $curlWrapper->getLastError() . "\n";
        exit;
    }

    $output_array = json_decode($output_data, true);
    if (! $output_array) {
        echo "ERREUR - impossible de décoder le JSON \n";
        exit;
    }

    if (isset($output_array['status']) && $output_array['status']  == "error") {
        echo "ERREUR Pastell : " . $output_array['error-message'] . "\n";
        exit;
    }


    if (isset($post_data['output'])) {
        $output = $post_data['output'];

        foreach ($output as $key => $global_key) {
            if (empty($output_array[$key])) {
                echo "ERREUR : la clé $key n'est pas présente dans la réponse \n";
                exit;
            }
            $global_data[$global_key] =  $output_array[$key];
        }
    }

    echo "Données sauvegardées : \n";
    print_r($global_data);
    echo "\n***********************\n";
}
