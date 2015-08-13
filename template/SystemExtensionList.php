<div class="box">

<table style='width:100%;'>
<tr>
<td>
<h2>Extensions install�es</h2>
</td>
<?php if ($droitEdition) : ?>
<td class='align_right'>
	<a href="system/extension-edition.php" class='btn'>Nouveau</a>
</td>
<?php endif;?>
</tr>
</table>

<table class='table table-striped'>
<tr>
	<th class="w200">Nom symbolique</th>
	<th>Connecteurs-Type</th>
	<th>Connecteurs</th>
	<th>Flux</th>
	<th>Num�ro de version (r�vision)</th>
	<th>Version de Pastell attendue</th>
	<th>Extensions attendues</th>
	<th>Module ok</th>
</tr>
<?php $i=0; foreach($all_extensions as $id_e => $extension) : ?>
	<tr>
		<td><a href='system/extension.php?id_extension=<?php hecho($id_e) ?>'><?php hecho($extension['id']); ?></a></td>
		<td>
			<ul>
			<?php foreach($extension['connecteur-type'] as $connecteur_type) : ?>
				<li><?php hecho($connecteur_type)?></li>
			<?php endforeach;?>
			</ul>
		</td>
		<td>
			<ul>
			<?php foreach($extension['connecteur'] as $connecteur) : ?>
				<li><?php hecho($connecteur)?></li>
			<?php endforeach;?>
			</ul>
		</td>
		<td>
			<ul>
			<?php foreach($extension['flux'] as $flux) : ?>
				<li>
				<?php hecho($flux)?>
				</li>
			<?php endforeach;?>
			</ul>
		</td>
		<td>
			<?php if ($extension['manifest']['version']) : ?>
				<?php hecho($extension['manifest']['version'])?>
			<?php else:?>
				<span class='text_alert'>NON VERSIONN�E</span>
			<?php endif;?>
			&nbsp;
			(<?php hecho($extension['manifest']['revision'])?>)
			<?php if ($extension['manifest']['autre-version-compatible']) : ?>
				<br/>Versions compatibles :
				<ul>
					<?php foreach($extension['manifest']['autre-version-compatible'] as $version) : ?>
					<li><?php hecho($version)?></li>
					<?php endforeach;?>
				</ul> 
			<?php endif;?>
			
		</td>
		<td>
			<?php hecho($extension['manifest']['pastell-version'])?>
			<?php if(! $extension['pastell-version-ok']) : ?>
			&nbsp;-&nbsp;<span class='text_alert'>KO</span>
			<?php endif;?>
		</td>
		<td>
		<ul>
		<?php foreach($extension['manifest']['extension_needed'] as $extension_needed => $extension_needed_info) : ?>
			<li><?php hecho($extension_needed)?>(version <?php hecho($extension_needed_info['version'])?>) 
				<?php if (! $extension_needed_info['extension_presente']) :?>
					<span class='text_alert'>KO</span>
				<?php elseif (! $extension_needed_info['extension_version_ok']) :?>
					<span class='text_alert'>Version KO</span>
				<?php endif;?>
				
				
			</li>
		<?php endforeach;?>
		</ul>
		</td>
		<td>
			<?php if ($extension['error']) : ?>
			<p class='alert alert-error'>
				<?php hecho($extension['error'])?>
			</p>
			<?php endif;?>
			<?php if ($extension['warning']) : ?>
			<p class='alert'>
				<?php hecho($extension['warning'])?>
			</p>
			<?php endif;?>
		</td>
		
	</tr>
<?php endforeach;?>
</table>
</div>

<div class="box">
<h2>Graphe des d�pendances des extensions</h2>
<img src="img/extensions_graphe/extensions_graphe.jpg" alt="Graphe des d�pendances des extensions" />
</div>

<div class="box">
<h2>Version de Pastell</h2>
<div class='alert alert-info'>Cette instance de Pastell est compatible avec les extensions qui n�cessitent une des versions de Pastell suivante:</div>
<ul>
<?php foreach($pastell_manifest['extensions_versions_accepted'] as $version) : ?>
<li>
<?php hecho($version)?>
</li>
<?php endforeach;?>
</ul>
</div>

