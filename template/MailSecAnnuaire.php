<?php
/** @var Gabarit $this */
?>
<a class='btn btn-link' href='Entite/detail?id_e=<?php echo $id_e ?>&page=5'><i class="fa fa-arrow-left"></i>&nbsp;Administration de <?php echo $infoEntite['denomination']?></a>

<div class='box'>

<a class='btn btn-link' href='MailSec/groupeList?id_e=<?php echo $id_e ?>'><i class='fa fa-eye'></i>&nbsp;Visualiser les groupes</a>
&nbsp;&nbsp;&nbsp;&nbsp;
<a class='btn btn-link' href='MailSec/groupeRoleList?id_e=<?php echo $id_e ?>'><i class='fa fa-eye'></i>&nbsp;Visualiser les groupes basés sur les rôles</a>

</div>

<div class="box">

<table style='width:100%;'>
<tr>
<td>
<h2>Liste des contacts de <?php echo $infoEntite['denomination'] ?></h2>
</td>
<?php if ($can_edit) : ?>
<td class='align_right'>
<a href="MailSec/import?id_e=<?php echo $id_e ?>" class='btn'><i class="fa fa-upload"></i>&nbsp;Importer</a>
</td>
<?php endif;?>
</tr>
</table>

<form action='MailSec/annuaire' method='get' class="form-inline">
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>'/>
	<input type='text' name='search' value='<?php echo $search?>'/>
	<select name='id_g'>
		<option value=''>Tous les groupes</option>
		<?php foreach($groupe_list as $groupe): ?>
			<option value='<?php echo $groupe['id_g']?>' <?php echo $id_g==$groupe['id_g']?"selected='selected'":""?>><?php hecho($groupe['nom'])?></option>
		<?php endforeach;?>
	</select>
	<button type='submit' class='btn'><i class='fa fa-search'></i>&nbsp;Rechercher</button>
</form>

<?php 
$this->SuivantPrecedent($offset,$limit,$nb_email,"MailSec/annuaire?id_e=$id_e&search=$search");
?>

<form action='MailSec/delete' method='post' >
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />

<table  class="table table-striped">
	<tr>
	
		<th>Description</th>
		<th>Email</th>
		<th>Groupes</th>
	</tr>
<?php foreach($listUtilisateur as $utilisateur) : ?>
	<tr>
		<td>
		<?php if ($can_edit) : ?>
			<input type='checkbox' name='id_a[]' value='<?php hecho($utilisateur['id_a']) ?>'/>
		<?php endif; ?>
		<a href='MailSec/detail?id_a=<?php echo $utilisateur['id_a'] ?>&id_e=<?php echo $id_e ?>'><?php echo $utilisateur['description']?></a></td>
		<td><?php echo $utilisateur['email']?></td>
		<td>
			<?php foreach($utilisateur['groupe'] as $i => $groupe) : ?>	
				<a href='MailSec/groupe?id_e=<?php echo $groupe['id_e']?>&id_g=<?php echo $groupe['id_g']?>'><?php echo $groupe['nom']?></a><?php if ($i != count($utilisateur['groupe']) - 1) : ?>, <?php endif;?>
			<?php endforeach;?>
		</td>
	</tr>
<?php endforeach;?>
	
</table>

<?php if ($can_edit) : ?>
    <button type="submit" class="btn btn-danger">
        <i class="fa fa-trash"></i>&nbsp;Supprimer
    </button>
<?php endif; ?>
</form>

</div>

<?php if ( $this->RoleUtilisateur->hasDroit($this->Authentification->getId(),"annuaire:edition",$id_e)) : ?>
<div class="box">
<h2>Ajouter un contact</h2>
<form action='MailSec/addContact' method='post' >
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	
	<table class="table table-striped">

			<tr>
				<th>Description</th>
				<td><input type='text' name='description' value='<?php echo $this->LastError->getLastInput('description') ?>' /></td>
			</tr>
			<tr>
				<th>Email</th>
				<td><input type='text' name='email' value='<?php echo $this->LastError->getLastInput('email') ?>'/></td>
			</tr>

	</table>
	<button type='submit' class='btn'><i class='fa fa-plus-circle'></i>&nbsp;Ajouter</button>
</form>
</div>
<?php endif;?>

<a class='btn btn-secondary' href='MailSec/export?id_e=<?php echo $id_e?>'><i class='fa fa-upload'></i>&nbsp;Exporter l'annuaire (CSV)</a>
