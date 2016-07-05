<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='Entite/detail?id_e=<?php echo $id_e ?>&page=3'><i class='icon-circle-arrow-left'></i>Retour</a>


<div class="box">

<h2>Ajouter un connecteur</h2>
<form action='<?php $this->url("/Connecteur/doNew") ?>' method='post' >
	<?php $this->displayCSRFInput() ?>
<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
<table class='table table-striped'>

<tr>
<th class='w200'>Libellé de l'instance</th>
<td><input type='text' name='libelle' value=''/></td>
</tr>

<tr>
<th>Connecteur</th>
<td><select name='id_connecteur' class="input-xxlarge">
		<?php foreach($all_connecteur_dispo as $id_connecteur => $connecteur) : ?>
			<option value='<?php hecho($id_connecteur)?>'>
				<?php hecho($connecteur[ConnecteurDefinitionFiles::NOM])?> (<?php hecho($connecteur[ConnecteurDefinitionFiles::TYPE])?>)
			</option>
		<?php endforeach;?>
	</select></td>
</tr>

</table>
<input type='submit' class='btn' value='Créer un connecteur' />
</form>
</div>
<br/><br/>