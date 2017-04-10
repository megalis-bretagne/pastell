<?php
/**
 * @var array $connecteur_frequence_list
 *
 */
?>
<div class='box'>
	<h2>Liste des fréquences</h2>
	<table class="table table-striped">
		<tr>
			<th>Connecteur(s)</th>
			<th>Action(s)</th>
			<th>Expression de fréquence</th>
			<th>&nbsp;</th>
		</tr>
		<?php foreach($connecteur_frequence_list as $connecteur_frequence_info) : ?>
			<tr>
				<td>
					<?php echo $connecteur_frequence_info['connecteur_selector'] ?>
				</td>
				<td>
					<?php echo $connecteur_frequence_info['action_selector'] ?>
				</td>
				<td>
					<?php echo $connecteur_frequence_info['expression'] ?>

				</td>
				<td>
					<a class='btn btn-primary' href='<?php $this->url("Daemon/connecteurFrequenceDetail?id_cf={$connecteur_frequence_info['id_cf']}") ?>'>Détail</a>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>
