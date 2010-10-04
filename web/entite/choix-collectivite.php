<?php 

require_once( dirname(__FILE__) . "/../init-authenticated.php");
require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once( PASTELL_PATH . "/lib/entite/EntiteListe.class.php");

$recuperateur = new Recuperateur($_GET);
$id_d = $recuperateur->get('id_d');
$id_e =  $recuperateur->get('id_e');

$entiteListe = new EntiteListe($sqlQuery);

$liste = $entiteListe->getAll(Entite::TYPE_COLLECTIVITE);

$page_title = "Veuillez choisir le ou les destinataires du document ";

include( PASTELL_PATH ."/include/haut.php");
?>



<div class="box_contenu clearfix">

<h2>Collectivit�</h2>

<form action='document/action.php' method='post'>
	<input type='hidden' name='id_d' value='<?php echo $id_d?>' />
	<input type='hidden' name='id_e' value='<?php echo $id_e?>' />
	<input type='hidden' name='action' value='Envoyer' />

<table class="tab_01">
	<tr>
		<th>&nbsp;</th>
		<th>D�nomination</th>
		<th>Siren</th>
	</tr>
<?php 
$cpt = 0;
foreach($liste as $i => $entite) : 
	$cpt++;
	?>
	<tr class='<?php echo $i%2?'bg_class_gris':'bg_class_blanc'?>'>
		<td class="w30"><input type='checkbox' name='destinataire[]' id="label_denomination_<?php echo $cpt ?>" value='<?php echo $entite['id_e']?>'/></td>
		<td><label for="label_denomination_<?php echo $cpt ?>"><?php echo $entite['denomination']?></label></td>
		<td>
		<a href='entite/detail.php?siren=<?php echo $entite['siren']?>'><?php echo $entite['siren']?></a>
		</td>

	</tr>
<?php endforeach; ?>
</table>
<div class="align_right">
<input type='submit' value='Envoyer le document' class='submit' />
</div>
</form>
</div>



<?php 
include( PASTELL_PATH ."/include/bas.php");
