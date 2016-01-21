
<a class='btn btn-mini' href='connecteur/edition.php?id_ce=<?php echo $connecteur_entite_info['id_ce'] ?>'><i class='icon-circle-arrow-left'></i>Revenir � la d�finition du connecteur</a>

<div class="box">
<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?>
</h2>

<form action='connecteur/edition-libelle-controler.php' method='post' >
	<input type='hidden' name='id_ce' value='<?php echo $connecteur_entite_info['id_ce'] ?>' />
<table class='table table-striped'>

<tr>
<th class='w200'>Libell� de l'instance</th>
<td><input type='text' name='libelle' value='<?php hecho($connecteur_entite_info['libelle']) ?>'/>
</td>
</tr>

	<tr>
		<th>Fr�quence d'utilisation</th>
		<td>
			<input type='text' name='frequence_en_minute' value='<?php hecho($connecteur_entite_info['frequence_en_minute']) ?>'/> minute(s)
		</td>
	</tr>
	<tr>
		<th>Verrou exclusif
			<p class='form_commentaire'>Deux connecteurs avec le m�me verrou ne peuvent �tre execut� simultan�ment.</p>

		</th>
		<td>
			<input type='text' name='id_verrou' value='<?php hecho($connecteur_entite_info['id_verrou']) ?>'/>
		</td>
	</tr>

</table>
	
	<input type='submit' class='btn' value='Modifier les propri�t�s'/>
</form>

</div>
