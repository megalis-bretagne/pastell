<?php
/**
 * @var array $connecteur_frequence_info
 */
?>
<a class='btn btn-mini' href='Daemon/config'>
	<i class='icon-circle-arrow-left'></i>revenir à la liste des fréquences
</a>
<div class="box">
	<h2>Détail d'une fréquence</h2>
	<table class='table table-striped'>
		<tr>
			<th class='w200'>Type</th>
			<td><?php echo $connecteur_frequence_info['type_connecteur'] ?></td>
		</tr>
		<tr>
			<th class='w200'>Famille de connecteurs</th>
			<td><?php echo $connecteur_frequence_info['famille_connecteur'] ?></td>
		</tr>
		<tr>
			<th class='w200'>Connecteur</th>
			<td><?php echo $connecteur_frequence_info['id_connecteur'] ?></td>
		</tr>
		<tr>
			<th class='w200'>Type d'action</th>
			<td><?php echo $connecteur_frequence_info['action_type'] ?></td>
		</tr>
		<?php if($connecteur_frequence_info['action_type'] == 'document') : ?>
		<tr>
			<th class='w200'>Type de document</th>
			<td><?php echo $connecteur_frequence_info['type_document'] ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th class='w200'>Action</th>
			<td><?php echo $connecteur_frequence_info['action'] ?></td>
		</tr>
		<tr>
			<th class='w200'>Fréquence</th>
			<td><?php echo $connecteur_frequence_info['expression'] ?></td>
		</tr>

	</table>
	<a class='btn'
	   href="<?php $this->url("Daemon/editFrequence?id_cf={$connecteur_frequence_info['id_cf']}") ?>"
	>
		Modifier
	</a>
	<a class='btn btn-danger'
	   href="<?php $this->url("Daemon/deleteFrequence?id_cf={$connecteur_frequence_info['id_cf']}") ?>"
	>
		Supprimer
	</a>
</div>