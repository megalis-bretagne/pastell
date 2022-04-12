<?php

require_once(__DIR__ . "/../../init.php");

#LANCEMENT EN www-data obligatoire !

if (getmyuid() == 0) {
    echo "Vous devez lancer le script en www-data/apache2\n";
    exit;
}

if ($argc < 5) {
    echo "{$argv[0]} id_e flux_id action_id folder\n";
    echo "Importe des documents dans Pastell à partir d'une sauvegarde\n";
    echo "Les documents sont crée dans l'état action_id\n";
    echo "Exemple : su www-data -c 'php add-file-from-workspace-copy.php 38 pdf-generique termine ../../temp/data/workspace/'";
    exit;
}


$id_e = $argv[1];
$flux_id = $argv[2];
$action_id = $argv[3];
$folder = $argv[4];

echo "Récupération depuis $folder vers $id_e:$flux_id dans l'état $action_id\n";

$all = glob("$folder/*/*/*.yml");
echo count($all) . " fichiers trouvés\n";

$documentSQL = $objectInstancier->getInstance(DocumentSQL::class);
$documentEntiteSQL = $objectInstancier->getInstance(DocumentEntite::class);
$actionCreatorSQL = $objectInstancier->getInstance(ActionCreatorSQL::class);
$donneesFormulaireFactory = $objectInstancier->getInstance(DonneesFormulaireFactory::class);

foreach ($all as $file) {
    $id_d = pathinfo($file, PATHINFO_FILENAME);

    echo "Récupération du dossier $id_d\n";

    if ($documentSQL->getInfo($id_d)) {
        echo "Le dossier $id_d existe déjà dans la base de données: [PASS]\n";
        continue;
    }


    $destination = WORKSPACE_PATH . "/{$id_d[0]}/{$id_d[1]}/";
    if (! file_exists($destination)) {
        echo "Création du repertoire $destination\n";
        mkdir($destination, 0755, true);
    }

    $all_file_to_copy = glob("$folder/{$id_d[0]}/{$id_d[1]}/$id_d.yml*");

    foreach ($all_file_to_copy as $file_to_copy) {
        echo "Copie de $file_to_copy\n";
        copy($file_to_copy, $destination . "/" . basename($file_to_copy));
    }
    echo "Création du document $id_d ($flux_id)\n";
    $documentSQL->save($id_d, $flux_id);
    $documentEntiteSQL->addRole($id_d, $id_e, 'editeur');
    $actionCreatorSQL->addAction($id_e, 0, "creation", "Restauration du document", $id_d);
    $actionCreatorSQL->addAction($id_e, 0, $action_id, "Restauration du document", $id_d);

    $donneesFormulaire = $donneesFormulaireFactory->get($id_d);
    $documentSQL->setTitre($id_d, $donneesFormulaire->getTitre());


    echo "Document $id_d : [RESTAURE]\n";
    exit;
}
