<?php
require_once( __DIR__ . "/../init.php");


if (count($argv) != 5){
	echo "{$argv[0]} : Modifie l'état ensemble de document\n";
	echo "Usage : {$argv[0]} id_e type_document ancien_etat nouvel_etat\n";
	exit;
}

$id_e = get_argv(1);
$type = get_argv(2);
$ancien_etat = get_argv(3);
$nouvel_etat = get_argv(4);


try {
	$all_doc = $objectInstancier->getInstance(DocumentActionEntite::class)->getDocument($id_e,$type,$ancien_etat);
	foreach($all_doc as $document_info){
		echo "Modification de : {$document_info['id_d']}\n";
		//$this->objectInstancier->ActionChange->addAction($document_info['id_d'],$id_e,0,$nouvel_etat,"Modification via le script bulk-set-etat");
	}
	echo count($all_doc)." documents ont été modifiés\n";
} catch (Exception $e){
	echo $e->getMessage()."\n";
}




