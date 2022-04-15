<?php
// TODO à supprimer

/*
 * script d'installation et d'intégration à pastell d'extensions
 *
 * ex parametres:
 * $url_pastell = http://pastell.exemple.fr/
 * $dir = /data/extensions
 * $ext = ptl_actes
 *
 * ex d'appel pour installer (dans $dir) et integrer à pastell l'extension ptl-actes et ses dependances (recursivement):
 * php install-extension.php http://pastell.exemple.fr /data/extensions ptl-actes
 */

if (count($argv) != 4) {
    echo "Installe et integre une extension pastell\n";
    echo "Usage : {$argv[0]} url_pastell repertoire_extension id_extension\n";
    exit;
}

$url = $argv[1] . "/api/edit-extension.php";
$dir = $argv[2];
$ext = $argv[3];

try {
    echo integre_extension($ext);
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}

function integre_extension($ext)
{

    global $url, $dir;
    $info = "";

    // installation de l'extension $ext dans le repertoire $dir
    if (!(file_exists($dir . '/' . $ext))) {
        $checkout = 'svn checkout https://scm.adullact.net/iruiz/svn/' . $ext . '/trunk ' . $dir . '/' . $ext;
        exec($checkout);
    }

    // integration de l'extension $ext à pastell
    $post_data = [
        'path' => $dir . "/" . $ext,
    ];
    $retour = pastell_api($url, $post_data);
    $info .= "Integration de l'extension " . $ext . ": ";

    if (empty($retour)) {
        return $info . "KO\n";
    }

    if (!empty($retour["error-message"])) {
        return $info . $retour["error-message"] . "\n";
    }

    if (!empty($retour["result"])) {
        $info .= $retour["result"] . "\n";
        foreach ($retour['detail_extension']['manifest']['extension_needed'] as $extension_needed => $extension_needed_info) {
            if (! $extension_needed_info['extension_presente']) {//Manque dependance
                $info .= "dependance: " . integre_extension($extension_needed) . "\n"; // recursivite
            } else {
                $info .= "dependance: " . $extension_needed . " presente\n";
            }
        }
    }
    return $info . "\n";
}

// fonction curl
function pastell_api($url, $post_data)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERPWD, "admin:admin");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    $output = curl_exec($curl);
    if ($err = curl_error($curl)) {
        echo "Error : " . $err;
    }
    return json_decode($output, true);
}
