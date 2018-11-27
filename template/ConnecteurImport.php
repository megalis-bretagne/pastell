<?php
/** @var Gabarit $this */
?>
<a class='btn' href='Connecteur/edition?id_ce=<?php echo $connecteur_entite_info['id_ce']?>'>
	<i class="fa fa-arrow-left"></i>&nbsp;Retour à la définition du connecteur
</a>

<div class="box">
	<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?>
	</h2>

	<form action='<?php $this->url("Connecteur/doImport") ?>' method='post'  enctype="multipart/form-data">
		<?php $this->displayCSRFInput() ?>
		<input type='hidden' name='id_ce' value='<?php echo $connecteur_entite_info['id_ce'] ?>' />
		<table class='table table-striped'>

			<tr>
				<th class='w200'>Fichier à importer (*.json)</th>
				<td><input type='file' name='pser' />
				</td>
			</tr>

		</table>

        <button type="submit" class="btn">
            <i class="fa fa-upload"></i>&nbsp;Importer
        </button>
	</form>

</div>
