<h2>Listes des flux</h2>
<table class="table table-striped">
		<tr>
				<th>Flux
					<br/>
					<em>Id du flux</em>
				</th>
				
				<th>Type de connecteur</th>
				<th>Connecteur</th>
				<th>Hérité</th>
				<th>&nbsp;</th>
		</tr>
<?php 
$i = 0;
foreach($all_flux as $id_flux => $flux_definition) : 
	$documentType = $this->DocumentTypeFactory->getFluxDocumentType($id_flux);
	foreach($documentType->getConnecteur() as $j=>$connecteur_type) : 
	?>
	<tr>
		<?php if ($j == 0) :?>
		<td rowspan='<?php echo (count($documentType->getConnecteur()))?>'><strong><?php hecho($documentType->getName() );?></strong>
			<br/>
			<em><?php hecho($id_flux)?></em>
		</td>
		
		<?php endif;?>
		<td><?php echo $connecteur_type;?></td>
		<td>
			<?php if (isset($all_flux_entite[$id_flux][$connecteur_type])) : ?>
				
			<a href='connecteur/edition.php?id_ce=<?php echo $all_flux_entite[$id_flux][$connecteur_type]['id_ce'] ?>'><?php hecho($all_flux_entite[$id_flux][$connecteur_type]['libelle']) ?></a>
				&nbsp;(<?php hecho($all_flux_entite[$id_flux][$connecteur_type]['id_connecteur']) ?>)
			<?php else:?>
			AUCUN
			<?php endif;?>	
		</td>
		<td>
			<?php if (isset($all_flux_entite[$id_flux][$connecteur_type])) : ?>
				<?php if ($all_flux_entite[$id_flux][$connecteur_type]['id_e'] != $id_e) : ?>
					<em> de <a href='entite/detail.php?id_e=<?php echo $all_flux_entite[$id_flux][$connecteur_type]['id_e'];?>'><?php echo $all_flux_entite[$id_flux][$connecteur_type]['denomination']; ?></a></em>
				<?php endif;?>
			<?php endif;?>
		&nbsp;
		</td>
		<td>
			<a class='btn btn-mini' href='flux/edition.php?id_e=<?php echo $id_e?>&flux=<?php hecho($id_flux)?>&type=<?php echo $connecteur_type ?>'>Choisir un connecteur</a>
		</td>
	</tr>
	<?php endforeach;?>
<?php endforeach;?>
</table>