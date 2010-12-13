<?php
require_once( dirname(__FILE__) . "/../init-authenticated.php");
require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once( PASTELL_PATH . "/lib/mailsec/Annuaire.class.php");

$recuperateur = new Recuperateur($_GET);
$id_e = $recuperateur->getInt('id_e');

if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:lecture",$id_e)){
	header("Location: ".SITE_BASE."index.php");
	exit;
}

$annuaire = new Annuaire($sqlQuery,$id_e);

$listUtilisateur = $annuaire->getUtilisateur();


$entite = new Entite($sqlQuery,$id_e);
$infoEntite = $entite->getInfo();

$page= "Carnet d'adresse";
$page_title= $infoEntite['denomination'] . " - Carnet d'adresse";

include( PASTELL_PATH ."/include/haut.php");
include(PASTELL_PATH . "/include/bloc_message.php");

?>
<a href='entite/detail.php?id_e=<?php echo $id_e ?>&page=3'>� Administration de <?php echo $infoEntite['denomination']?></a>
<a href='mailsec/groupe-list.php?id_e=<?php echo $id_e ?>'>Voir les groupes �</a>

<br/><br/>
<div class="box_contenu">
<h2>liste des contacts de <?php echo $infoEntite['denomination'] ?> </h2>

<form action='mailsec/del-contact.php' method='post' >		
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />

<table  class="tab_02">
	<tr>
	
		<th>Description</th>
		<th>Email</th>
		
	</tr>
<?php foreach($listUtilisateur as $utilisateur) : ?>
	<tr>
		<td><input type='checkbox' name='email[]' value='<?php echo $utilisateur['email'] ?>'/><?php echo $utilisateur['description']?></td>
		<td><?php echo $utilisateur['email']?></td>
	</tr>
<?php endforeach;?>
	
</table>
<input type='submit' value='Supprimer'/>
</form>
</div>

<?php if ( $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) : ?>

<div class="box_contenu">
<h2>Ajouter un contact</h2>
<form action='mailsec/add-contact.php' method='post' >		
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	
	<table>
		<tbody>
			<tr>
				<th>Description</th>
				<td><input type='text' name='description' value='<?php echo $lastError->getLastInput('description') ?>' /></td>
			</tr>
			<tr>
				<th>Email</th>
				<td><input type='text' name='email' value='<?php echo $lastError->getLastInput('email') ?>'/></td>
			</tr>
		</tbody>
	</table>
	<input type='submit' value='Ajouter'/>
</form>
</div>
<?php endif;?>
<?php include( PASTELL_PATH ."/include/bas.php");
