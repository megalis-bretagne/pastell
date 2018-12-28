<?php
$id_e = $entiteExtendedInfo['id_e'];
?>
<?php if (! $entiteExtendedInfo['is_active']): ?>
	<div class="alert alert-danger">Cette collectivité est désactivée</div>
<?php endif; ?>

<div class="box">
<h2>Informations générales</h2>
<table class='table table-striped'>		
	<tr>
		<th class='w200'>Type</th>
		<td><?php echo Entite::getNom($entiteExtendedInfo['type']) ?></td>
	</tr>
	
	<tr>
		<th>Dénomination</th>
		<td><?php echo $entiteExtendedInfo['denomination'] ?></td>
	</tr>
	<?php if ($entiteExtendedInfo['siren']) : ?>
		<tr>
			<th>Siren</th>
			<td><?php echo $entiteExtendedInfo['siren'] ?></td>
		</tr>
	<?php endif;?>
	<tr>
		<th>Date d'inscription</th>
		<td><?php echo time_iso_to_fr($entiteExtendedInfo['date_inscription']) ?></td>
	</tr>
	<?php if ($entiteExtendedInfo['entite_mere']) : ?>
	<tr>
		<th>Entité mère</th>
		<td>
			<a  href='Entite/detail?id_e=<?php echo $entiteExtendedInfo['entite_mere']['id_e']?>'>
				<?php echo $entiteExtendedInfo['entite_mere']['denomination'] ?>
			</a>
		</td>
	</tr>
	<?php endif;?>

	<?php if ($entiteExtendedInfo['cdg']) :
		$infoCDG = $entiteExtendedInfo['cdg']; ?>
		<tr>
			<th>Centre de gestion</th>
			<td>
				<?php if ($droit_lecture_cdg ) : ?>			
					<a href='Entite/detail?id_e=<?php echo $infoCDG['id_e']?>'>
						<?php echo $infoCDG['denomination']?>
					</a>
				<?php else : ?>
					<?php echo $infoCDG['denomination']?>
				<?php endif; ?>
				
				</td>
		</tr>
	<?php endif;?>

</table>

	<?php if ($droit_edition) : ?>
		<a class='btn btn-primary' href="Entite/edition?id_e=<?php echo $id_e?>">
            <i class="fa fa-pencil"></i>&nbsp;
			Modifier
		</a>
		<?php if ($is_supprimable) : ?>

			<a class='btn btn-danger' href='Entite/supprimer?id_e=<?php echo $id_e ?>'>
                <i class="fa fa-trash"></i>&nbsp;
                Supprimer


			</a>
		<?php endif;?>

		<a class='btn btn-warning' href='Entite/activer?id_e=<?php echo $id_e?>&active=<?php echo ! $entiteExtendedInfo['is_active']?>'>
			<?php if ($entiteExtendedInfo['is_active']) : ?>
				<i class="fa fa-toggle-on"></i>&nbsp;Désactiver
			<?php else :?>
                <i class="fa fa-toggle-off"></i>&nbsp;Activer
			<?php endif;?>

		</a>

	<?php endif;?>


</div>

<div class="box">
	<h2>Entités filles</h2>
	<?php if ( ! $entiteExtendedInfo['filles']) : ?>
		<div class="alert alert-info">
			Cette entité n'a pas d'entité fille.
		</div>
	<?php else :?>


	<table class='table table-striped'>
		<tr>
			<th>Dénomination</th>
			<th>Siren</th>
			<th>Type</th>
			<th>Active</th>
		</tr>
		<?php foreach($entiteExtendedInfo['filles'] as $fille) : ?>
			<tr>
				<td>
					<a href='Entite/detail?id_e=<?php echo $fille['id_e']?>'>
						<?php hecho($fille['denomination'])?>
					</a>
				<td><?php hecho($fille['siren']); ?></td>
				<td><?php hecho($fille['type']); ?></td>
				<td><?php echo $fille['is_active']?'':'Désactivée'; ?></td>
			</tr>
		<?php endforeach;?>

	</table>
	<?php endif; ?>

	<?php if ($droit_edition) : ?>
		<a class='btn' href="Entite/edition?entite_mere=<?php echo $id_e?>" >
            <i class="fa fa-plus"></i>&nbsp;Créer une entité fille
		</a>&nbsp;&nbsp;
		<a class='btn' href="Entite/import?id_e=<?php echo $id_e?>" >
            <i class="fa fa-upload"></i>&nbsp;Importer des entités filles
		</a>
	<?php endif;?>
	&nbsp;&nbsp;<a class='btn' href='<?php $this->url("Entite/export?id_e={$id_e}"); ?>'>
        <i class="fa fa-download"></i>&nbsp;Exporter (CSV)
	</a>
</div>