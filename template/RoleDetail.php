<?php
/** @var Gabarit $this */
?>
<?php $i = 0; ?>

<a class='btn btn-link' href='<?php $this->url("Role/index") ?>'><i class="fa fa-arrow-left"></i>&nbsp;Retour à la liste des rôles</a>


<div class="box">

<h2>Liste des droits - <?php  hecho($role_info['libelle']) ?></h2>
<a class='btn btn-secondary' href='<?php $this->url("Role/edition?role={$role}") ?>'><i class='fa fa-pencil'></i>&nbsp;Modifier le libellé</a>

<br/><br/>

<form action='<?php $this->url("Role/doDetail") ?>' method='post'>
	<?php $this->displayCSRFInput() ?>
	<table class="table table-striped table-hover">
		<tr>
			<th>Droits</th>
		</tr>
		<?php foreach($all_droit_utilisateur as $droit => $ok) : ?>
			<tr>
				<td>
					<?php if ($role_edition) : ?>
						<input type='checkbox' name='droit[]' value='<?php echo $droit ?>' <?php echo $ok?"checked='checked'":"" ?>/>&nbsp;
					<?php endif;?>
					<?php echo $droit ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php if ($role_edition) : ?>
		<input type='hidden' name='role' value='<?php echo $role?>'/>
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-pencil"></i>&nbsp;Modifier
        </button>
	<?php endif;?>
</form>



</div>

<div class="box">
<h2>Supprimer le rôle</h2>

<form action='<?php $this->url("Role/doDelete") ?>' method='post'>
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='role' value='<?php hecho($role) ?>' />
    <button type="submit" class="btn btn-danger">
        <i class="fa fa-trash"></i>&nbsp;Supprimer
    </button>
</form>
</div>