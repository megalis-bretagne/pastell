<?php
/** @var Gabarit $this */
?>
<div class="box">
<h2>Associations connecteurs globaux</h2>

<table class="table table-striped">
		<tr>
				<th class="w200">Type de connecteur</th>
				<th class="w200">Connecteur</th>
				<th>&nbsp;</th>
		</tr>
<?php 
$i = 0;

foreach($all_connecteur_type as $connecteur_type => $global_connecteur) :

	
	?>
	<tr>
		<td><?php echo $connecteur_type;?></td>
		<td>
			<?php if ($global_connecteur) : ?>
			<a href='<?php $this->url("Connecteur/edition?id_ce={$all_flux_global[$connecteur_type]['id_ce']}") ?>'><?php hecho($all_flux_global[$connecteur_type]['libelle']) ?></a>
				&nbsp;(<?php hecho($all_flux_global[$connecteur_type]['id_connecteur']) ?>)
			<?php else:?>
			AUCUN
			<?php endif;?>	
		</td>
		<td>
			<a class='btn btn-mini' href='<?php $this->url("Flux/edition?id_e={$id_e}&type={$connecteur_type}"); ?>'>Choisir un connecteur</a>
		</td>
	</tr>
	<?php endforeach;?>

</table>
</div>