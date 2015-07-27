<a class='btn btn-mini' href='<?php echo "mailsec/annuaire.php?id_e={$id_e}" ?>'><i class='icon-circle-arrow-left'></i><?php hecho($entite_info['denomination']) ?></a>

<div class="box">
	<form action="mailsec/do-import.php" method='post' enctype='multipart/form-data'>
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
	<p>Les lignes sont format�s de la mani�re suivante : 
	"Email";"Description";"Groupe ..."</p>
	<p>Note: si le fichier est trop gros (&gt;  <?php echo ini_get("upload_max_filesize") ?>) 
	vous pouvez le compresser avec gzip.
	</p>
	<p>Les emails d�j� existants verront leur propri�t�s (description, groupe(s)) remplac�es </p>
</div>