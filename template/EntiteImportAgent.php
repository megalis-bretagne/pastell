<?php
/** @var Gabarit $this */
?>
<div class="box">
	<form action="Entite/importAgent" method='post' enctype='multipart/form-data'>
		<?php $this->displayCSRFInput() ?>
		<input type='hidden' name='id_e' value='<?php hecho($entite_info['id_e'])?>' />
		
		<table class="table">
		<?php if ($entite_info['id_e']) : ?>
		<tr>
			<th class='w200'>Collectivité (écrasera le SIREN du fichier) :</th>
			<td><?php echo $entite_info['denomination'] ?></td>
		</tr>
		<?php endif;?>
		<tr>
			<th class='w200'>Fichier CSV</th>
			<td><input type='file' name='csv_agent'/></td>
		</tr>
            <tr>
                <th class='w200'>Supprimer tous les agents</th>
                <td><input type='checkbox' name='delete_all'/></td>
            </tr>
		</table>
		<input type="submit" value="Importer" class="btn" />
	</form>
</div>

<div class="alert alert-info">
	<p><strong>Format du fichier</strong></p>
	<p>Le fichier CSV doit contenir un agent par ligne.</p>
	<p>Les lignes sont formatées de la manière suivante :
	"Matricule (5)";"Titre";"Nom d'usage";"Nom patronymique";"Prénom";"Emploi / Grade (C)";"Emploi / Grade (L)";"Collectivité (C)";"Collectivité (L)";"SIREN";"Type de dossier";"Type de dossier (L)";"Train de traitement (C)";"Train de traitement (L)"</p>
	<p>Note: si le fichier est trop gros (&gt;  <?php echo ini_get("upload_max_filesize") ?>) 
	vous pouvez le compresser avec gzip.
	</p>
</div>