#! /usr/bin/php
<?php
require_once( __DIR__ . "/../init.php");

$flux = get_argv(1);

if (!$flux){
    echo "Usage : {$argv[0]} flux\n";
    exit;
}

$result = $objectInstancier->Document->getAllByType($flux);

if (!$result){
    echo "Il n'y a pas de document de type $flux\n";
    exit;
}

$entite_list = array();

foreach($result as $document){

    $id_d = $document['id_d'];
    $doc_entite = $objectInstancier->DocumentEntite->getEntite($id_d);
    foreach ($doc_entite as $entite) {
        $id_e = $entite['id_e'];

        echo 'Entite: ' . "{$id_e}" . ', document: ' . "{$id_d} \n";

        if (!in_array($id_e, $entite_list)) {
            $entite_list[] = $id_e;
        }

        $id_job = $objectInstancier->JobQueueSQL->getJobIdForDocument($id_e, $id_d);
        if ($id_job) {
            echo 'Le job: ' . "{$id_job} sera supprimé pour ce document\n";
        }
    }

}
echo "\n";

$nb_document = count($result);
echo "$nb_document documents vont être supprimés !\n\n";


foreach($entite_list as $id_e){

    $flux_list = $objectInstancier->FluxEntiteSQL->getAllFluxEntite($id_e, $flux);
    $nb_flux = count($flux_list);
    echo "$nb_flux association de flux vont être supprimés pour l'entité $id_e\n";
    echo "\n";

}

echo "Etes-vous sur (o/N) ? ";
$fh = fopen('php://stdin', 'r');
$entree = trim(fgets($fh,1024));

if ($entree != 'o'){
    exit;
}

foreach($result as $document){

    $id_d = $document['id_d'];
    $doc_entite = $objectInstancier->DocumentEntite->getEntite($id_d);
    foreach ($doc_entite as $entite) {
        $id_e = $entite['id_e'];
        $id_job = $objectInstancier->JobQueueSQL->getJobIdForDocument($id_e, $id_d);
        if ($id_job) {
            $objectInstancier->JobQueueSQL->deleteJob($id_job);
        }
    }

    $info = $objectInstancier->Document->getInfo($id_d);
    $objectInstancier->DonneesFormulaireFactory->get($id_d)->delete();
    $objectInstancier->Document->delete($id_d);

    $message = "Le document « {$info['titre']} » ($id_d) a été supprimé par un administrateur";
    $objectInstancier->Journal->add(Journal::DOCUMENT_ACTION,0,$id_d,"suppression",$message);

}

foreach($entite_list as $id_e){

    $flux_list = $objectInstancier->FluxEntiteSQL->getAllFluxEntite($id_e, $flux);
    foreach($flux_list as $flux){
        $objectInstancier->FluxEntiteSQL->removeConnecteur($flux['id_fe']);
    }

}

echo "Les elements ont ete supprimés\n";






