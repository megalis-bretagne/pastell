<?php
require_once( dirname(__FILE__) . "/../init-authenticated.php");


$recuperateur = new Recuperateur($_GET);
$id_ce = $recuperateur->getInt('id_ce');

$objectInstancier->ConnecteurControler->hasDroitOnConnecteur($id_ce);


$connecteur_entite_info = $objectInstancier->ConnecteurEntiteSQL->getInfo($id_ce);
$entite_info = $objectInstancier->EntiteSQL->getInfo($connecteur_entite_info['id_e']);

$afficheurFormulaire = $objectInstancier->AfficheurFormulaireFactory->getFormulaireConnecteur($id_ce);

if ($connecteur_entite_info['id_e']){
	$action = $objectInstancier->DocumentTypeFactory->getEntiteDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
} else {
	$action = $objectInstancier->DocumentTypeFactory->getGlobalDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
} 


if (! $connecteur_entite_info['id_e']){
	$entite_info['denomination'] = "Entit� racine";
}
$page_title = "Configuration des connecteurs pour � {$entite_info['denomination']} �";


include( PASTELL_PATH ."/include/haut.php");
?>
<?php include(PASTELL_PATH . "/include/bloc_message.php");?>


<a href='connecteur/edition.php?id_ce=<?php echo $id_ce?>'>� Revenir � <?php echo $connecteur_entite_info['libelle']?></a>
<br/><br/>
<div class="box_contenu clearfix">
<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?> 
</h2>
<?php 

$afficheurFormulaire->affiche(0,"connecteur/edition-modif-controler.php",
									"connecteur/recuperation-fichier.php?id_ce=$id_ce",
									"connecteur/supprimer-fichier.php?id_ce=$id_ce",
									"connecteur/external-data.php"); 

?></div>
<?php 
include( PASTELL_PATH ."/include/bas.php");
