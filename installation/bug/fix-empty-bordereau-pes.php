<?php

//Un bug mettait un nom de fichier sur les bordereau PES helios, mais pas le contenu du fichier (à prendre depuis le connecteur)

/**
 * TODO: Remove in 4.0
 * @var ObjectInstancier $objectInstancier
 * @var SQLQuery $sqlQuery
 */

$id_e = 1;

require_once __DIR__ . '/../../init.php';


$sql = "SELECT document.id_d FROM document " .
        " JOIN document_entite ON document.id_d=document_entite.id_d " .
        " WHERE id_e=? AND type=?";

$id_d_list = $sqlQuery->queryOneCol($sql, $id_e, 'helios-generique');

$donneesFormulaireFactory = getDonneesFormulaireFactory();

$connecteurFactory = new ConnecteurFactory($objectInstancier);

$signatureForm = $connecteurFactory->getConnecteurConfigByType($id_e, 'helios-generique', 'signature');

$visuel_pdf_defaut_name = $signatureForm->getFileName('visuel_pdf_default');
$visuel_pdf_defaut = $signatureForm->getFileContent('visuel_pdf_default');
if (! $visuel_pdf_defaut) {
    echo "Impossible de trouver le visuel PDF par défaut !\n";
    exit;
}

foreach ($id_d_list as $id_d) {
    $donneesFormulaire = $donneesFormulaireFactory->get($id_d, 'helios-generique');
    if (! $donneesFormulaire->get('visuel_pdf')) {
        continue;
    }

    $file_content = $donneesFormulaire->getFileContent('visuel_pdf');
    if ($file_content) {
        continue;
    }

    echo "PROBLEME sur $id_d\n";
    $donneesFormulaire->addFileFromData('visuel_pdf', $visuel_pdf_defaut_name, $visuel_pdf_defaut);
}

/**
 * @return DonneesFormulaireFactory
 */
function getDonneesFormulaireFactory()
{
    global $objectInstancier;
    return $objectInstancier->getInstance(DonneesFormulaireFactory::class);
}
