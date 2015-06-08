<a class='btn btn-mini' href='system/index.php?page_number=<?php echo $this->SystemControler->getPageNumber('extensions')?>'>
	<i class='icon-circle-arrow-left'></i>Liste des extensions
</a>

<div class="box">
<table style='width:100%;'>
<tr>
<td>
<h2>Extension <?php hecho($extension_info['nom'])?></h2>
</table>

<?php if ($extension_info['error']) : ?>
	<div class='alert alert-error'>
		<?php hecho($extension_info['error-detail'])?>
	</div>
<?php endif ?>



<?php if ($extension_info['warning']) : ?>
	<div class='alert alert'>
		<?php hecho($extension_info['warning-detail'])?>
	</div>
<?php endif ?>

<table class='table table-striped'>
<tr>
	<th>Emplacement de l'extension sur le syst�me de fichier</th>
	<td><?php hecho($extension_info['path']); ?></td>
</tr>
</table>
 
<a href='system/extension-edition.php?id_extension=<?php echo $extension_info['id_e']?>' class='btn'>Modifier</a>
<a href='system/extension-delete.php?id_e=<?php echo $extension_info['id_e']?>' class='btn btn-danger' onclick='return confirm("�tes-vous s�r de vouloir supprimer cette extension ?")'>Supprimer</a>
</div>


<div class='box'>
<h2>Contenu du fichier manifest</h2>
<table class='table table-striped'>
<tr>
	<th>Nom symbolique</th>
	<td><?php hecho($extension_info['nom']) ?></td>
</tr>
<tr>
	<th>Nom</th>
	<td><?php hecho($extension_info['manifest']['nom']) ?></td>
</tr>
<tr>
	<th>Description</th>
	<td><?php hecho($extension_info['manifest']['description']) ?></td>
</tr>
<tr>
	<th>Num�ro de version de l'extension</th>
	<td><?php hecho($extension_info['manifest']['version']) ?></td>
</tr>
<tr>
	<th>Num�ro de version compatible de l'extension</th>
	<td>
		<ul>
		<?php foreach($extension_info['manifest']['autre-version-compatible'] as $version) : ?>
			<li><?php hecho($version) ?></li>
		<?php endforeach;?>
		</ul>
	</td>
</tr>

<tr>
	<th>Version de Pastell attendue</th>
	<td>
		<?php hecho($extension_info['manifest']['pastell-version']) ?>
	</td>
</tr>
<tr>
	<th>Extensions attendues</th>
	<td>
		<ul>
		<?php foreach($extension_info['manifest']['extension_needed'] as $extension_needed => $extension_needed_info) : ?>
			<li><?php hecho($extension_needed)?>&nbsp;(version <?php hecho($extension_needed_info['version'])?>) 
				<?php if (! $extension_needed_info['extension_presente']) :?>
					<span class='text_alert'>KO</span>
				<?php elseif (! $extension_needed_info['extension_version_ok']) :?>
					<span class='text_alert'>Version KO</span>
				<?php endif;?>
			</li>
		<?php endforeach;?>
		</ul>
	</td>
</tr>


</table>
</div>		


<div class="box">
<h2>Connecteurs</h2>
<table class='table table-striped'>
<tr>
	<th>Nom</th>
	<th>Description</th>
</tr>
<?php foreach($extension_info['connecteur'] as $connecteur) : ?>
				<tr>
					<td><b><?php hecho($connecteur)?></b></td>
					<td>
					<?php 
					$connecteur_info = $this->ConnecteurDefinitionFiles->getInfo($connecteur);
					?>
					<?php if (isset($connecteur_info['description'])) : ?>
						<?php echo nl2br($connecteur_info['description']); ?>
					<?php endif;?>
					</td>
				</tr>
<?php endforeach;?>
</table>
</div>		
		
<div class="box">
<h2>Flux</h2>
<table class='table table-striped'>
<tr>
	<th>Nom</th>
	<th>Description</th>
</tr>
<?php foreach($extension_info['flux'] as $flux) : ?>
				<tr>
					<td><b><?php hecho($flux)?></b></td>
					<td>
					<?php $flux_info = $this->FluxDefinitionFiles->getInfo($flux); ?>
					<?php if (isset($flux_info['description'])) : ?>
						<?php echo nl2br($flux_info['description']); ?>
					<?php endif;?>
					</td>
				</tr>
<?php endforeach;?>
</table>
</div>

		