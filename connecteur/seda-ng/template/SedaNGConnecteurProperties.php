<a class="btn btn-mini" href="Connecteur/edition?id_ce=<?php echo $id_ce?>"><i class="icon-circle-arrow-left"></i>Retour à la définition du connecteur</a>
<div class="box">
	<h2>Propriétés constantes lors de la génération des bordereaux</h2>
<form action='<?php $this->url("Connecteur/doExternalData") ?>' method='post'>
    <?php $this->displayCSRFInput();?>
	<input type="hidden" name="id_e" value="<?php echo $id_e ?>"/>
	<input type="hidden" name="id_ce" value="<?php echo $id_ce ?>"/>
	<input type="hidden" name="field" value="<?php echo $field ?>" />
	<input type="hidden" name="go" value="true"/>

	<table class="table table-striped">
	<?php foreach($properties as $property => $value) : ?>
		<tr>
			<td><?php hecho($property) ?></td>
			<td><input name="<?php hecho($property) ?>" value="<?php hecho($value)?>"/></td>
		</tr>
	<?php endforeach; ?>
	</table>

	<input type="submit" class="btn">
</form>
</div>