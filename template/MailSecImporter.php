<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='<?php echo "MailSec/annuaire?id_e={$id_e}" ?>'><i class='icon-circle-arrow-left'></i><?php hecho($entite_info['denomination']) ?></a>

<div class="box">
	<form action="MailSec/doImport" method='post' enctype='multipart/form-data'>
		<?php $this->displayCSRFInput(); ?>
		<input type='hidden' name='id_e' value='<?php hecho($id_e)?>' />
		
		<table class="table">
		<tr>
			<th class='w200'>Fichier CSV</th>
			<td><input type='file' name='csv'/></td>
		</tr>
		</table>
		<input type="submit" value="Importer" class="btn" />
	</form>
</div>

<div class="alert alert-info">
	<p><strong>Format du fichier</strong></p>
	<p>Le fichier CSV doit contenir une adresse email par ligne.</p>
	<p>Les lignes sont formatées de la manière suivante :
	"Email";"Description";"Groupe ..."</p>
	<p>Note: si le fichier est trop gros (&gt;  <?php echo ini_get("upload_max_filesize") ?>) 
	vous pouvez le compresser avec gzip.
	</p>
	<p>Les emails déjà existants verront leur propriétés (description, groupe(s)) remplacées </p>
</div>