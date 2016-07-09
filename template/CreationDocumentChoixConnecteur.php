<?php
/** @var Gabarit $this */
?>
<form action='<?php $this->url("Connecteur/doExternalData") ?>' method='post'>
	<?php $this->displayCSRFInput();?>
<input type='hidden' name='id_ce' value='<?php echo $id_ce?>' />
<input type='hidden' name='field' value='<?php echo $field?>' />
<select name='connecteur_creation'>
	<?php foreach($recuperation_connecteur_list as $id_ce => $libelle) : ?>
		<option value='<?php hecho($id_ce) ?>'><?php hecho($libelle)?></option>
	<?php endforeach; ?>
	</select>	
	<input type='submit' class='btn' value='Sélectionner'/>
</form>