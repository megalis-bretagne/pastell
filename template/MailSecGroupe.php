<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='MailSec/groupeList?id_e=<?php echo $id_e ?>'><i class='icon-circle-arrow-left'></i> Voir tous les groupes</a>

<br/><br/>
<div class="box">
<h2>Liste des contacts de «<?php echo $infoGroupe['nom']?>» </h2>

<?php $this->SuivantPrecedent($offset,AnnuaireGroupe::NB_MAX,$nbUtilisateur,"MailSec/groupe?id_e=$id_e&id_g=$id_g"); ?>



<form action='MailSec/delContactFromGroupe' method='post' >
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='id_g' value='<?php echo $id_g ?>' />

<table  class="table table-striped">
	<tr>
	
		<th>Description</th>
		<th>Email</th>
		
	</tr>
<?php foreach($listUtilisateur as $utilisateur) : ?>
	<tr>
		<td><input type='checkbox' name='id_a[]' value='<?php echo $utilisateur['id_a'] ?>'/><a href='MailSec/detail?id_a=<?php echo $utilisateur['id_a']?>'><?php echo $utilisateur['description']?></a></td>
		<td><?php echo $utilisateur['email']?></td>
	</tr>
<?php endforeach;?>
	
</table>
<?php if ($can_edit) : ?>
<input type='submit' value='Enlever du groupe' class='btn'/>
<?php endif; ?>

</form>
</div>

<?php if ( $roleUtilisateur->hasDroit($authentification->getId(),"annuaire:edition",$id_e)) : ?>

<div class="box">
<h2>Ajouter un contact à «<?php echo $infoGroupe['nom']?>» </h2>
<form action='MailSec/addContactToGroupe' method='post' >
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='id_g' value='<?php echo $id_g ?>' />
	
	<table class="table table-striped">
		<tbody>
			<tr>
				<th>Contact : </th>
				<td><input type='text' id='nom_contact' name='name' value='' /></td>
			</tr>	
		</tbody>
	</table>
	<script>
	 
 		 $(document).ready(function(){
				$("#nom_contact").pastellAutocomplete("MailSec/getContactAjax",<?php echo $id_e?>,true);

 		 });
	</script>
	<input type='submit' value='Ajouter' class='btn'/>
</form>
</div>
<?php endif;?>


<div class="box">
<h2>Partage</h2>

<?php if ($infoGroupe['partage']) : ?>
<div class='box_info'>
<p>Ce groupe est actuellement partagé avec les entités-filles (services, collectivités) de <?php  echo $infoEntite['denomination'] ?> qui peuvent l'utiliser 
pour leur propre mail.</p>
</div>
<form action='MailSec/partageGroupe' method='post' >
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='id_g' value='<?php echo $id_g ?>' />
	<input type='submit' value='Supprimer le partage' class='btn'/>
</form>
<?php else:?>
<div class='box_info'>
<p>Cliquer pour partager ce groupe avec les entités filles de <?php  echo $infoEntite['denomination'] ?>.</p>
</div>
	<form action='MailSec/partageGroupe' method='post' >
		<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='id_g' value='<?php echo $id_g ?>' />
	<input type='submit' value='Partager' class='btn'/>
</form>
<?php endif;?>

</div>
