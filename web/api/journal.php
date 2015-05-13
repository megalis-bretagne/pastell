<?php

require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
$offset = $recuperateur->getInt('offset',0);
$limit = $recuperateur->getInt('limit',100);
$id_e = $recuperateur->getInt('id_e',0);
$type = $recuperateur->get('type');
$id_d = $recuperateur->get('id_d');
$id_user = $recuperateur->get('id_user');
$recherche = $recuperateur->get('recherche');
$date_debut = $recuperateur->get('date_debut');
$date_fin = $recuperateur->get('date_fin');
$format = $recuperateur->get('format');
$csv_entete_colonne = $recuperateur->getInt('csv_entete_colonne', 0);

if   (! $roleUtilisateur->hasDroit($id_u,"journal:lecture",$id_e)){
	$JSONoutput->displayErrorAndExit("Acces interdit id_e=$id_e, id_d=$id_d,id_u=$id_u,type=$type");
}

// Pour �viter des probl�mes m�moires, au format CSV : 
//  - Utilisation de Pdo. La lecture du recordset se fait ligne � ligne. Pas de chargement de la totalit� du recordset en m�moire.
//  - comme le parcours des lignes peut �tre long, r�initialisation du temps max_execution_time la chaque boucle.
//  - G�n�ration du fichier csv dans le r�pertoire /tmp puis retourn�
// NB : Le probl�me "m�moire", existe toujours pour le format JSON.

if ($format == 'csv') {

    $filecsv = tempnam('/tmp/', 'exportjournal');
    $handle = fopen($filecsv, 'w');    
        
    $max_execution_time= ini_get('max_execution_time');
    
    $pdo = $sqlQuery->getPdo();    
    list($sql, $param_sql) = $journal->getQueryAll($id_e, $type, $id_d, $id_user, $offset, $limit, $recherche, $date_debut, $date_fin, true);
    $stmt = $pdo->prepare($sql);    
    $stmt->execute($param_sql);    
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ini_set('max_execution_time', $max_execution_time);    
        if ($csv_entete_colonne) {
            // Les ent�tes sont les cl�s du tableau associatif
            $entetes = array_keys($row);                
            // Suppression de la colonne preuve
            $index_col_preuve = array_search('preuve', $entetes, true);
            array_splice($entetes, $index_col_preuve, 1);
            // Compatibilit� avec l'existant et journal->getAll() : ajout de 2 colonnes suppl�mentaires
            $entetes[] = 'document_type_libelle';
            $entetes[] = 'action_libelle';
            fputcsv($handle, $entetes);
            $csv_entete_colonne=false;
        }        
        $row['message'] = preg_replace("/(\r\n|\n|\r)/", " ", $row['message']);
        $row['message_horodate'] = preg_replace("/(\r\n|\n|\r)/", " ", $row['message_horodate']);        
        unset($row['preuve']);
        $documentType = $objectInstancier->DocumentTypeFactory->getFluxDocumentType($row['document_type']);
        // Compatibilit� avec l'existant et journal->getAll() : ajout de 2 colonnes suppl�mentaires
        $row['document_type_libelle'] = $documentType->getName();
        $row['action_libelle'] = $documentType->getAction()->getActionName($row['action']);
        fputcsv($handle, $row);
    }

    fclose($handle);
    //Export du fichier
    header("Content-type: text/csv; charset=iso-8859-1");
    header("Content-disposition: attachment; filename=pastell-export-journal-$id_e-$type-$id_d.csv");
    readfile($filecsv);
    // Suppression du fichier temporaire apr�s l'export
    unlink($filecsv);

} else {
    $all = $journal->getAll($id_e, $type, $id_d, $id_user, $offset, $limit, $recherche, $date_debut, $date_fin);
    $JSONoutput->display($all);    
}
