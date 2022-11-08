<?php

/**
 * @var SQLQuery $sqlQuery
 */
//TODO à mettre dans un connecteur global #1356

require_once __DIR__ . '/../init.php';


if (($argc < 2) || ! preg_match("#\d{4}-\d{2}-\d{2}#", $argv[1])) {
    echo "Usage: {$argv[0]} date_debut\n";
    echo "\tAvec date_debut au format ISO (exemple : 2015-03-31)\n";
    exit;
}

$date = $argv[1];


$sql = "SELECT journal_historique.*,document.titre,entite.denomination, utilisateur.nom, utilisateur.prenom,entite.siren  FROM journal_historique " .
    " LEFT JOIN document ON journal_historique.id_d = document.id_d " .
    " LEFT JOIN entite ON journal_historique.id_e = entite.id_e " .
    " LEFT JOIN utilisateur ON journal_historique.id_u = utilisateur.id_u " .
    " WHERE journal_historique.date >= ?";



$sqlQuery->prepareAndExecute($sql, $date);
$CSVoutput = new CSVoutput();
$CSVoutput->displayHTTPHeader("pastell-export-journal-$date.csv");

$CSVoutput->begin();
while ($sqlQuery->hasMoreResult()) {
    $data = $sqlQuery->fetch();
    unset($data['preuve']);
    $CSVoutput->displayLine($data);
}
$CSVoutput->end();
