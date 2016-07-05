<?php
/** @var Gabarit $this */
?>
<div class='box'>

<h2><?php echo $titreSelectAction?></h2>

<form action='Utilisateur/doNotificationEdit.php' method='post'>
	<?php $this->displayCSRFInput() ?>
<input type='hidden' name='id_u' value='<?php echo $id_u?>'/>
<input type='hidden' name='id_e' value='<?php echo $id_e?>'/>
<input type='hidden' name='type' value='<?php echo $type?>'/>
<table class="table table-striped">
<tr>
	<th><input type="checkbox" name="select-all" id="select-all" /></th>
	<th>Nom de l'action</th>
</tr>
<?php foreach($action_list as $action):?>
<tr>
	<td><input type='checkbox' name='<?php hecho($action['id'])?>' <?php echo $action['checked']?'checked="checked"':'' ?>/> </td>
	<td>
		<?php hecho($action['action_name']) ?>
	</td>
</tr>
<?php endforeach;?>
</table>

<input type='submit' value='Modifier' class='btn' />

</form>
</div>

<div class='alert alert-warning'>
<p>Toutes ces actions ne produisent pas forcément des notifications !</p>
<p>La notification est envoyée lorsque le document entre dans l'état correspondant</p>
</div>

