<?php
require_once(__DIR__ . "/../../init.php");

/*
* Reprise de données pastell 1.4.11.
* Script qui permet de corriger les enregistrements deffectueux de la table document_action_entite suite au Bug Fix:
* envoi scm : Bug fix: Si 2 actions de document se déroulaient à la même seconde alors l'identifiant de l'action id_a pouvait être identique (enregistré en doublon) dans la table document_action_entite (r1979)
*/

$to_update = array();

// Liste les doublons id_a de la table document_action_entite

$sql = "SELECT COUNT(document_action_entite.id_a) AS nb_doublon, document_action_entite.id_a, MAX(document_action_entite.id_j) AS max_id_j " .
    " FROM document_action_entite " .
    " GROUP BY document_action_entite.id_a " .
    " HAVING COUNT(document_action_entite.id_a)>1 ";

$all_doublons = $sqlQuery->query($sql);

echo count($all_doublons) . " doublons dans document_action_entite:\n\n";


// Pour chaque doublon id_a, on récupère le plus grand id_a du document correspondant dans la table document_action

foreach ($all_doublons as $doublon_id_a) {
    echo $doublon_id_a['nb_doublon'] . " doublons pour id_a = " . $doublon_id_a['id_a'] . " (max_id_j = " . $doublon_id_a['max_id_j'] . ") dans document_action_entite " . "\n";

    $sql = "SELECT MAX(da1.id_a) AS id_a, da1.id_d, da1.id_e " .
        " FROM document_action da1 " .
        " WHERE EXISTS (SELECT da2.id_a FROM document_action da2 " .
        "        WHERE da2.id_a = ? AND da2.id_d = da1.id_d)" .
        " GROUP BY da1.id_d, da1.id_e";

    $all_max_id_a = $sqlQuery->query($sql, $doublon_id_a['id_a']);

    foreach ($all_max_id_a as $max_id_a) {
        echo "Avec comme plus grand id_a = " . $max_id_a['id_a'] . " du doc id_d = " . $max_id_a['id_d'] . " id_e = " . $max_id_a['id_e'] . " dans document_action " . "\n";

        $sql = "SELECT count(document_action_entite.id_a) " .
            " FROM document_action_entite " .
            " WHERE document_action_entite.id_a = ? ";

        $nb =  $sqlQuery->queryOne($sql, $max_id_a['id_a']);

        if ($nb) { //si le max_id_a existe dans la table document_action_entite, on ne fait rien, la dernière action est ok
            echo "OK, La derniere action id_a = " . $max_id_a['id_a'] . " existe dans document_action_entite. On ne fait rien. " . "\n";
        } else { // sinon on corrige dans la table document_action_entite
            echo "KO, La derniere action id_a = " . $max_id_a['id_a'] . " n'existe pas dans document_action_entite. On update le doublon ayant le max(id_j) " . "\n";
            $to_update[] = array (
                "id_j" => $doublon_id_a['max_id_j'],
                "id_a" => $max_id_a['id_a'],
            );
        }
        echo "\n";
    }
}

if (count($to_update) == 0) {
    echo count($to_update) . " enregistrements à modifier.\n";
} else {
    echo count($to_update) . " enregistrements de document_action_entite vont etre modifies.\n";
    echo "Etes-vous sur (o/N) ? ";
    $fh = fopen('php://stdin', 'r');
    $entree = trim(fgets($fh, 1024));

    if ($entree != 'o') {
        exit;
    }

    foreach ($to_update as $enreg) {
        echo "id_a " . $enreg['id_a'] . " id_j " . $enreg['id_j'] . "\n";

        $sql = "UPDATE document_action_entite " .
            " SET id_a=?" .
            " WHERE document_action_entite.id_j=?";
        $sqlQuery->query($sql, $enreg['id_a'], $enreg['id_j']);
    }
    echo "Les documents ont ete modifies\n";
}
