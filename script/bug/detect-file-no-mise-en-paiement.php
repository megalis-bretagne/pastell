<?php
//TODO à supprimer

require_once __DIR__ . "/../../init.php";

$id_e = $argv[1];

$documentActionEntite = $objectInstancier->getInstance(DocumentActionEntite::class);

$document_list = $documentActionEntite->getDocument($id_e, 'facture-chorus-fournisseur', 'termine');

$donneesFormulaireFactory = $objectInstancier->getInstance(DonneesFormulaireFactory::class);

foreach ($document_list as $document_info) {
    $donneesFormulaire = $donneesFormulaireFactory->get($document_info['id_d']);
    echo "{$document_info['last_action_date']} - {$document_info['id_d']}: " . $donneesFormulaire->get('statut_facture') . "\n";
}
