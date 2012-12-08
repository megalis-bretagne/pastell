<?php
require_once( dirname(__FILE__) . "/../init-authenticated.php");

$recuperateur = new Recuperateur($_GET);
$id_ce = $recuperateur->getInt('id_ce');

$objectInstancier->ConnecteurControler->hasDroitOnConnecteur($id_ce);


$connecteur_entite_info = $objectInstancier->ConnecteurEntiteSQL->getInfo($id_ce);

if ($connecteur_entite_info['id_e']){
	$entite_info = $objectInstancier->EntiteSQL->getInfo($connecteur_entite_info['id_e']);
} else {
	$entite_info['denomination'] = "Entit� racine";
}


$afficheurFormulaire = $objectInstancier->AfficheurFormulaireFactory->getFormulaireConnecteur($id_ce);


if ($connecteur_entite_info['id_e']){
	$action = $objectInstancier->DocumentTypeFactory->getEntiteDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
} else {
	$action = $objectInstancier->DocumentTypeFactory->getGlobalDocumentType($connecteur_entite_info['id_connecteur'])->getAction();
} 


$page_title = "Configuration des connecteurs pour � {$entite_info['denomination']} �";


include( PASTELL_PATH ."/include/haut.php");
?>
<?php include(PASTELL_PATH . "/include/bloc_message.php");?>


<a href='entite/detail.php?id_e=<?php echo $connecteur_entite_info['id_e']?>&page=3'>� Revenir � <?php echo $entite_info['denomination']?></a>
<br/><br/>
<div class="box_contenu clearfix">
<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?> 
<a href="connecteur/edition-modif.php?id_ce=<?php hecho($id_ce) ?>" class='btn_maj'>
			Modifier
		</a>

</h2>
<?php 

$afficheurFormulaire->afficheStatic(0,"connecteur/recuperation-fichier.php?id_ce=$id_ce"); 
 
$action_possible = $objectInstancier->ActionPossible->getActionPossibleOnConnecteur($id_ce,$authentification->getId());
 
foreach($action_possible as $action_name) : ?>
<form action='connecteur/action.php' method='post' >
	<input type='hidden' name='id_ce' value='<?php echo $id_ce ?>' />
	<input type='hidden' name='action' value='<?php echo $action_name ?>' />
	<input type='submit' value='<?php hecho($action->getActionName($action_name)) ?>'/>
</form>
<?php endforeach;?>

</div>



<div class="box_contenu clearfix">
<h2>Autres op�rations</h2>

<ul>
<li><a href="connecteur/edition-libelle.php?id_ce=<?php echo $id_ce?>" >
	Modifier le libell� du connecteur (<?php hecho($connecteur_entite_info['libelle'])?>)
</a></li>
<li><a href="connecteur/delete.php?id_ce=<?php echo $id_ce?>" >
			Supprimer ce connecteur 
</a></li>
</ul>
</div>

<?php 
include( PASTELL_PATH ."/include/bas.php");
