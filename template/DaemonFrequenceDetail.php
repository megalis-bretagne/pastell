<?php
/**
 * @var ConnecteurFrequence $connecteurFrequence
 * @var Gabarit $this
 */
?>
<a class='btn btn-mini' href='<?php $this->url("Daemon/config") ?>'>
	<i class='icon-circle-arrow-left'></i>revenir à la liste des fréquences
</a>
<div class="box">
	<h2>Détail d'une fréquence</h2>
	<table class='table table-striped'>
		<tr>
			<th class='w200'>Type</th>
			<td><?php echo $connecteurFrequence->type_connecteur ?></td>
		</tr>
		<tr>
			<th class='w200'>Famille de connecteurs</th>
			<td><?php echo $connecteurFrequence->famille_connecteur ?></td>
		</tr>
		<tr>
			<th class='w200'>Connecteur</th>
			<td><?php echo $connecteurFrequence->id_connecteur ?></td>
		</tr>
		<tr>
			<th class='w200'>Type d'action</th>
			<td><?php echo $connecteurFrequence->action_type ?></td>
		</tr>
		<?php if($connecteurFrequence->action_type == 'document') : ?>
		<tr>
			<th class='w200'>Type de document</th>
			<td><?php echo $connecteurFrequence->type_document ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th class='w200'>Action</th>
			<td><?php echo $connecteurFrequence->action ?></td>
		</tr>
		<tr>
			<th class='w200'>Fréquence</th>
			<td><?php echo $connecteurFrequence->expression ?></td>
		</tr>

	</table>
	<a class='btn'
	   href="<?php $this->url("Daemon/editFrequence?id_cf={$connecteurFrequence->id_cf}") ?>"
	>
		Modifier
	</a>
	<a class='btn btn-danger'
	   href="<?php $this->url("Daemon/deleteFrequence?id_cf={$connecteurFrequence->id_cf}") ?>"
	>
		Supprimer
	</a>
</div>