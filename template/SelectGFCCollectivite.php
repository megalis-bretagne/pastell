
<form action='document/external-data-controler.php' method='post'>
	<input type='hidden' name='id_d' value='<?php echo $id_d?>' />
	<input type='hidden' name='id_e' value='<?php echo $id_e?>' />
	<input type='hidden' name='page' value='<?php echo $page?>' />
	<input type='hidden' name='field' value='<?php echo $field?>' />

	Veuillez choisir une collectivité :
	<br/><br/>
	<select name='collectivitecible'>
	<?php foreach($infoCollectivite as $num => $coll) : ?>
			<option value='<?php hecho($webGFC->setInfo($num,$coll)) ?>'><?php echo utf8_decode($coll) ?></option>
	<?php endforeach; ?>
	</select>
	
	<input type='submit' class='btn' value='Sélectionner'/>
</form>

