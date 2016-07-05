<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='<?php $this->url("Connecteur/edition?id_ce={$connecteur_entite_info['id_ce']}") ?>'><i class='icon-circle-arrow-left'></i>Revenir à la définition du connecteur</a>

<div class="box">
<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?>
</h2>

<form action='<?php $this->url("Connecteur/doEditionLibelle") ?>' method='post' >
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_ce' value='<?php echo $connecteur_entite_info['id_ce'] ?>' />
<table class='table table-striped'>

<tr>
<th class='w200'>Libellé de l'instance</th>
<td><input type='text' name='libelle' value='<?php hecho($connecteur_entite_info['libelle']) ?>'/>
</td>
</tr>

	<tr>
		<th>Fréquence d'utilisation</th>
		<td>
			<input type='text' name='frequence_en_minute' value='<?php hecho($connecteur_entite_info['frequence_en_minute']) ?>'/> minute(s)
		</td>
	</tr>
	<tr>
		<th>Verrou exclusif
			<p class='form_commentaire'>Deux connecteurs avec le même verrou ne peuvent être executé simultanément.</p>

		</th>
		<td>
			<input type='text' name='id_verrou' value='<?php hecho($connecteur_entite_info['id_verrou']) ?>'/>
		</td>
	</tr>

</table>
	
	<input type='submit' class='btn' value='Modifier les propriétés'/>
</form>

</div>
