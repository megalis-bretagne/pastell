
<a class='btn btn-mini' href='connecteur/edition.php?id_ce=<?php echo $connecteur_entite_info['id_ce'] ?>'><i class='icon-circle-arrow-left'></i>Revenir à la définition du connecteur</a>

<div class="box">
	<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?>
	</h2>

	<form action='connecteur/import-controler.php' method='post'  enctype="multipart/form-data">
		<input type='hidden' name='id_ce' value='<?php echo $connecteur_entite_info['id_ce'] ?>' />
		<table class='table table-striped'>

			<tr>
				<th class='w200'>Fichier à importer (*.json)</th>
				<td><input type='file' name='pser' />
				</td>
			</tr>

		</table>

		<input type='submit' class='btn' value='Importer'/>
	</form>

</div>
