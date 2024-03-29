<?php
/** @var Gabarit $this */
?>
<div class="box">
<table style='width:100%;'>
<tr>
<td>
<h2>Listes des flux</h2>
</td>

<td class='align_right'>
<?php if($id_e_mere) : ?>
	<form action='<?php $this->url("Flux/toogleHeritage"); ?>' method='post' >
		<?php $this->displayCSRFInput(); ?>
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='flux' value='<?php echo FluxEntiteHeritageSQL::ALL_FLUX?>' />
	<?php if($all_herited) :?> 
		<em>Tous les flux sont hérités de la mère</em>
		
		<button type='submit' class='btn btn-mini'><i class='icon-minus'></i>&nbsp;Supprimer l'héritage</button>
	<?php else :?>
		<button type='submit' class='btn btn-mini'><i class='icon-plus'></i>&nbsp;Faire tout hériter</button>
	<?php endif;?>
</form>
<?php endif;?>
</td>

</tr>
</table>

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
		
<?php foreach($flux_connecteur_list as $connecteur_info) : ?> 
	<tr>
		<?php if ($connecteur_info['num_connecteur'] == 0) :?>
			<td rowspan='<?php echo $connecteur_info['nb_connecteur'] ?>'><strong><?php hecho($connecteur_info['nom_flux']);?></strong>
				<br/>
				<em><?php hecho($connecteur_info['id_flux']);?></em>
				<?php if($id_e_mere && ! $all_herited) : ?>
					<form action='<?php $this->url("Flux/toogleHeritage"); ?>' method='post' >
						<?php $this->displayCSRFInput(); ?>
					<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
					<input type='hidden' name='flux' value='<?php hecho($connecteur_info['id_flux']) ?>' />
					<?php if($connecteur_info['inherited_flux']) :?> 
						(flux hérité de la mère)
						<br/>
						<button type='submit' class='btn btn-mini'><i class='icon-minus'></i>&nbsp;Supprimer l'héritage</button>
					<?php else :?>
						<button type='submit' class='btn btn-mini'><i class='icon-plus'></i>&nbsp;Faire hériter</button>
					<?php endif;?>
				</form>
				<?php endif;?>
			</td>
		<?php endif;?>
		<td><?php echo $connecteur_info['connecteur_type'];?></td>
		<td>
			<?php if ($connecteur_info['connecteur_info']) : ?>
				
				<a href='<?php $this->url("Connecteur/edition?id_ce={$connecteur_info['connecteur_info']['id_ce']}") ?>'><?php hecho($connecteur_info['connecteur_info']['libelle']) ?></a>
					&nbsp;(<?php hecho($connecteur_info['connecteur_info']['id_connecteur']) ?>)
			<?php else:?>
			AUCUN
			<?php endif;?>	
		</td>
		<td>
			<?php if ($connecteur_info['connecteur_info']) : ?>
				<?php if ($connecteur_info['connecteur_info']['id_e'] != $id_e) : ?>
					<em> de <a href='Entite/detail?id_e=<?php echo $connecteur_info['connecteur_info']['id_e'];?>'><?php echo $connecteur_info['connecteur_info']['denomination']; ?></a></em>
				<?php endif;?>
			<?php endif;?>
		&nbsp;
		</td>
		<td>
			<?php if(! $connecteur_info['inherited_flux'] && ! $all_herited) :?> 
				<a class='btn btn-mini' href='<?php $this->url("Flux/edition?id_e=$id_e&flux={$connecteur_info['id_flux']}&type={$connecteur_info['connecteur_type']}"); ?>'>Choisir un connecteur</a>
			<?php endif;?>
		</td>
	</tr>
<?php endforeach;?>
</table>
</div>