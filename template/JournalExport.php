<?php
/** @var Gabarit $this */
?>

<div class="box">

<h2>Filtre du journal</h2>

<form action='Journal/doExport' method='post'>
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php hecho($id_e)?>'>
	<input type='hidden' name='id_d' value='<?php hecho($id_d)?>'>
	<input type='hidden' name='id_u' value='<?php hecho($id_u)?>'>
	<input type='hidden' name='type' value='<?php hecho($type)?>'>
	<table class='table table-striped'>
		<tr>
			<th class='w200'>Entité</th>
			<td><?php hecho($id_e?$entite_info['denomination']:"Toutes")?></td>
		</tr>
		<tr>
			<th>Utilisateur</th>
			<td><?php hecho($id_u?$utilisateur_info['login']:"Tous")?></td>
		</tr>
		<tr>
			<th>Document</th>
			<td><?php hecho($id_d?$document_info['titre']:"Tous")?></td>
		</tr>
		<tr>
			<th>Type</th>
			<td><?php hecho($type?:"Tous")?></td>
		</tr>
		<tr>
			<th><label for='input_recherche'>Recherche</label> </th>
			 <td> <input type='text' name='recherche' id='input_recherche' value='<?php hecho($recherche) ?>' /></td>
		</tr>
		<tr>
			<th><label for='date_debut'>
			Date de début
			</label> </th>
			 <td>
			 	<input type='text' id='date_debut' name='date_debut' value='<?php hecho(date_iso_to_fr($date_debut))?>' size='40'/>
			 </td>
		</tr>
		<tr>
			<th><label for='date_fin'>
			Date de fin
			</label> </th>
			 <td> 
			 	<input type='text' id='date_fin' name='date_fin' value='<?php hecho(date_iso_to_fr($date_fin))?>' />
			 </td>
		</tr>
		<tr>
			<th><label for="en_tete_colonne">Ajouter une ligne d'entête</label> </th>
			<td>
				<input type="checkbox" id="en_tete_colonne" name="en_tete_colonne" checked="checked"/>
			</td>
		</tr>


	</table>
    <a class='btn btn-secondary' href='Journal/index?id_e=<?php echo $id_e?>&id_d=<?php echo $id_d ?>&id_u=<?php echo $id_u ?>&type=<?php echo $type ?>&recherche=<?php hecho($recherche)?>'>
        <i class="fa fa-times-circle"></i>&nbsp;Annuler
    </a>

    <button type='submit' class='btn btn-primary'><i class="fa fa-download"></i>&nbsp;Récupérer le journal</button>
	
</form>
</div>

<script type="text/javascript">
jQuery.datepicker.setDefaults(jQuery.datepicker.regional['fr']);
$(function() {
	$("#date_debut").datepicker( { dateFormat: 'dd/mm/yy' });
	$("#date_fin").datepicker( { dateFormat: 'dd/mm/yy' });
});
</script>