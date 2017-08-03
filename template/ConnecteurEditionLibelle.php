<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='Connecteur/edition?id_ce=<?php echo $connecteur_entite_info['id_ce']?>'>
	<i class='icon-circle-arrow-left'></i>Retour à la définition du connecteur
</a>

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

</table>
	
	<input type='submit' class='btn' value='Modifier les propriétés'/>
</form>

</div>
