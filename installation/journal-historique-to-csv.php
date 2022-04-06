<?php

require_once(__DIR__ . "/../init.php");

//ex appel: php journal-historique-to-csv.php 01/06/2015 30/06/2015 > pastell-export-journal-historique.csv

$date_debut = get_argv(1); //format 01/06/2015
$date_fin = get_argv(2);

$date_debut = date_fr_to_iso($date_debut);
$date_fin = date_fr_to_iso($date_fin);

list($sql,$value) = getQueryAll($date_debut, $date_fin) ;

$sqlQuery->prepareAndExecute($sql, $value);
$CSVoutput = new CSVoutput();
$CSVoutput->displayHTTPHeader("pastell-export-journal-historique.csv");

$CSVoutput->begin();
while ($sqlQuery->hasMoreResult()) {
    $data = $sqlQuery->fetch();
    unset($data['preuve']);
    $CSVoutput->displayLine($data);
}
$CSVoutput->end();

function getQueryAll($date_debut = false, $date_fin = false)
{
    $value = [];
    $sql = "SELECT journal_historique.*,document.titre,entite.denomination, utilisateur.nom, utilisateur.prenom,entite.siren " .
            " FROM journal_historique " .
            " LEFT JOIN document ON journal_historique.id_d = document.id_d " .
            " LEFT JOIN entite ON journal_historique.id_e = entite.id_e " .
            " LEFT JOIN utilisateur ON journal_historique.id_u = utilisateur.id_u " .
            " WHERE 1=1 ";

    if ($date_debut) {
        $sql .= "AND DATE(journal_historique.date) >= ?";
        $value[] = $date_debut;
    }
    if ($date_fin) {
        $sql .= "AND DATE(journal_historique.date) <= ?";
        $value[] = $date_fin;
    }

    $sql .= " ORDER BY id_j DESC " ;
    return [$sql,$value];
}
