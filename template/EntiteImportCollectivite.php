<?php
/** @var Gabarit $this */
?>
<div class="box">
	<form action="Entite/doImport" method='post' enctype='multipart/form-data'>
		<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php hecho($entite_info['id_e'])?>' />
	<table class='table'>
		<?php if ($entite_info['id_e']) : ?>
		<tr>
			<th class='w140'>Collectivité parente</th>
			<td><?php echo $entite_info['denomination'] ?></td>
		</tr>
		<?php endif;?>
		
		<tr>
			<th class='w140'>Fichier CSV</th>
			<td><input type='file' name='csv_col'/></td>
		</tr>
		<tr>
			<th>Centre de gestion</th>
			<td><?php $this->render("CDGSelect"); ?></td>
		</tr>
	</table>
        <button type="submit" class="btn">
            <i class="fa fa-upload"></i>&nbsp;Importer
        </button>
	</form>
	</div>
	
	<div class="alert alert-info">
	<p><strong>Format du fichier</strong></p>
	<p>Le fichier CSV doit contenir une collectivité par ligne.</p>
	<p>Les lignes sont formatées de la manière suivante : "libellé collectivité";"siren"</p>
</div>