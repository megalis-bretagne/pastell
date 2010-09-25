<?php

require_once( dirname(__FILE__) . "/../init-authenticated.php");
require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once( PASTELL_PATH . "/lib/flux/FluxInscriptionFournisseur.class.php");
require_once( PASTELL_PATH . "/lib/utilisateur/UtilisateurListe.class.php");
require_once( PASTELL_PATH . "/lib/transaction/TransactionFinder.class.php");

$recuperateur = new Recuperateur($_GET);
$id_e = $recuperateur->getInt('id_e',0);

$entite = new Entite($sqlQuery,$id_e);
$info = $entite->getInfo();

if ( ! $roleUtilisateur->hasDroit($authentification->getId(),"entite:lecture",$id_e)){
	header("Location: index.php");
	exit;
}

$utilisateurListe = new UtilisateurListe($sqlQuery);

$lastTransaction = false;
if ($info['type'] == Entite::TYPE_FOURNISSEUR) {
	$transactionFinder = new TransactionFinder($sqlQuery);
	$lastTransaction = $transactionFinder->getLastTransactionBySiren($siren,FluxInscriptionFournisseur::TYPE);
}

$page_title = "D�tail " . $info['denomination'];

$infoMere = false;
if ($info['entite_mere']){
	$entiteMere = new Entite($sqlQuery,$info['entite_mere']);
	$infoMere = $entiteMere->getInfo();
}

$filles = $entite->getFille();

include( PASTELL_PATH ."/include/haut.php");
?>
<?php if ($info['type'] == Entite::TYPE_FOURNISSEUR) : ?>
<a href='entite/fournisseur.php'>� liste des fournisseurs</a>
<?php else :?>
<a href='entite/index.php'>� liste des collectivit�s</a>
<?php endif;?>
<br/><br/>


<div class="box_contenu clearfix">

<h2>Informations g�n�rales
<?php if ($roleUtilisateur->hasDroit($authentification->getId(),"entite:edition",$id_e)) : ?>
<a href="entite/edition.php?id_e=<?php echo $id_e?>" class='btn_maj'>
		Modifier
	</a>
<?php endif;?>
</h2>
	

<table class='tab_04'>

<tr>
<th>Type</th>
<td><?php echo Entite::getNom($info['type']) ?></td>
</tr>

<tr>
<th>D�nomination</th>
<td><?php echo $info['denomination'] ?></td>
</tr>

<tr>
<th>Siren</th>
<td><?php echo $info['siren'] ?></td>
</tr>

<?php if ($info['type'] == Entite::TYPE_FOURNISSEUR ) : ?>
<tr>
<th>Etat</th>

<td>
<?php if($lastTransaction) : ?>
<a href='<?php echo SITE_BASE ?>flux/detail-transaction.php?id_t=<?php echo $lastTransaction; ?>'>
<?php endif;?>
<?php echo Entite::getChaineEtat($info['etat']) ?> 
<?php if($lastTransaction) : ?>
</a>
<?php endif;?>

</td>
</tr>
<?php endif;?>
<tr>
<th>Date d'inscription</th>
<td><?php echo $info['date_inscription'] ?></td>
</tr>
<?php if ($infoMere) : ?>
<tr>
	<th>Entit� m�re</th>
	<td>
		<a href='entite/detail.php?siren=<?php echo $infoMere['siren']?>'>
			<?php echo $infoMere['denomination'] ?>
		</a>
	</td>
</tr>
<?php endif;?>
</table>
</div>

<?php if ($info['type'] != Entite::TYPE_FOURNISSEUR ) : ?>
<div class="box_contenu">
<h2>Liste des entit�s filles 
<?php if ($roleUtilisateur->hasDroit($authentification->getId(),"entite:edition",$id_e)) : ?>
	<a href="entite/edition.php?entite_mere=<?php echo $id_e?>" class='btn_add'>
		Nouveau
	</a>
<?php endif;?>
</h2>
	
	<?php if ($filles) : ?>
	
		<table class='tab_01'>
		<tr>
			<th>D�nomination</th>
			<th>siren</th>
		</tr>
		<?php foreach($filles as $fille) : ?>
			<tr>
				<td>
					<a href='entite/detail.php?id_e=<?php echo $fille['id_e']?>'>
						<?php echo $fille['denomination']?>
					</a>
				</td>
				<td><?php echo $fille['siren']?></td>
			</tr>
		<?php endforeach;?>
		</table>
	<?php else : ?>
		<p>Cette entit� n'a pas d'entit� fille</p>
	<?php endif;?>
</div>
<?php endif;?>

<div class="box_contenu">
<h2>Liste des utilisateurs<?php if ($info['type'] != Entite::TYPE_FOURNISSEUR ) :?>
<?php if ($roleUtilisateur->hasDroit($authentification->getId(),"entite:edition",$id_e)) : ?>
	<a href="utilisateur/edition.php?id_e=<?php echo $id_e ?>" class='btn_add'>
		Nouveau
	</a>
<?php endif;?>
<?php endif;?></h2>

<table class='<?php echo $info['type'] != Entite::TYPE_FOURNISSEUR?"tab_02":"tab_03" ?>'>
<tr>
	<th>Pr�nom Nom</th>
	<th>login</th>
	<th>email</th>
	<th>Role</th>
	
</tr>
<?php foreach($utilisateurListe->getUtilisateurByEntite($id_e) as $user) : ?>
	<tr>
		<td>
			<a href='utilisateur/detail.php?id_u=<?php echo $user['id_u'] ?>'>
				<?php echo $user['prenom']?> <?php echo $user['nom']?>
			</a>
		</td>
		<td><?php echo $user['login']?></td>
		<td><?php echo $user['email']?></td>
		<td><?php echo $user['role']?></td>
	</tr>
<?php endforeach; ?>

</table>


</div>
<?php if($info['type'] == Entite::TYPE_FOURNISSEUR): ?>
<a href='supprimer.php'>Redemander les informations</a>
<?php endif; ?>

<?php 
include( PASTELL_PATH ."/include/bas.php");
