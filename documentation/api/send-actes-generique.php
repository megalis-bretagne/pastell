<?php

/**
 * Ce script fonctionne avec PHP 7.2 (minimum)
 */

require_once __DIR__ . "/DefaultSettings.php";
require_once __DIR__ . "/PastellSender.php";

$pastellSender = new PastellSender(
    PASTELL_URL,
    PASTELL_LOGIN,
    PASTELL_PASSWORD,
    PASTELL_ID_E
);

try {

    /**
     * La première étape consiste à créer un dossier sur Pastell de type Actes générique
     */
    echo "Création du document sur Pastell\n";
    $id_d = $pastellSender->createDocument('actes-generique');
    echo "Le document $id_d a été créé sur Pastell\n\n";

    /**
     * La seconde étape consiste à modifier toutes les métadonnées indispensables
     */
    echo "Ajout des méta-données sur le document $id_d\n";
    $result = $pastellSender->modifDocument($id_d, [
        'acte_nature' => 3, /** Voir la norme @ctes */
        'objet' => "Achat d'un logiciel de dématérialisation",
        'numero_de_lacte' => 'TEST_' . mt_rand(0, mt_getrandmax()), /** Numéro interne de l'acte */
        'date_de_lacte' => date("Y-m-d"), /** Les dates Pastell sont toutes au format ISO */
        'envoi_tdt' => 'On', /** Indique qu'on souhaite envoyer le document au contrôle de légalité */
        'classification' => '2.1', /** Classfication en matière et sous-matière (norme @ctes) */
    ]);
    print_r($result);
    echo "Le document a été modifié\n";

    /**
     * On envoie les différents fichiers composant l'actes : la pièce principale et les annexes
     */
    echo "Envoi du fichier principale de l'actes\n";
    $result = $pastellSender->sendFile($id_d, "arrete", __DIR__ . "/vide.pdf");
    print_r($result);
    echo "L'acte a été envoyé\n";

    echo "Ajout d'une première annexe\n";
    $result = $pastellSender->sendFile(
        $id_d,
        "autre_document_attache",
        __DIR__ . "/vide.pdf",
        0
    );
    print_r($result);
    echo "La première annexe a été envoyée\n";

    echo "Ajout d'une seconde annexe\n";
    $result = $pastellSender->sendFile(
        $id_d,
        "autre_document_attache",
        __DIR__ . "/test-pastell-i-parapheur.pdf",
        1
    );
    print_r($result);
    echo "La seconde annexe a été envoyée\n";

    /**
     * On renseigne la typologie des pièces
     */
    echo "Renseignement de la typologie des pièces\n";
    $result = $pastellSender->modifExternalData($id_d, 'type_piece', ['type_pj' => array('99_AI','22_AG','22_AT')]);
    print_r($result);

    /**
     * Finalement, on envoie le dossier au TdT
     */
    echo "Envoi du dossier au TdT\n";
    $result = $pastellSender->actionOnDossier($id_d, 'send-tdt');
    print_r($result);

    echo "Le document a été envoyé au contrôle de légalité !\n";
} catch (Exception $e) {
    echo "Un problème est survenu dans le déroulement de l'envoi de l'actes\n";
    echo $e->getMessage() . "\n";
}
