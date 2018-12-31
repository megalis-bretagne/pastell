<?php
/** @var Gabarit $this */
?>
<?php if ($id_e) : ?>
	<a class='btn btn-link' href='Entite/detail?id_e=<?php echo $id_e?>'>
		<i class="fa fa-arrow-left"></i>&nbsp;retour à <?php echo $infoEntite['denomination']?>
	</a>
<?php elseif ($entite_mere) : ?>
	<a class='btn btn-link' href='Entite/detail?id_e=<?php echo $infoMere['id_e']?>'>
		<i class="fa fa-arrow-left"></i>&nbsp;retour à <?php echo $infoMere['denomination']?>
	</a>
<?php else: ?>
	<a class='btn btn-link' href='Entite/detail'>
		<i class="fa fa-arrow-left"></i>&nbsp;Retour à la liste des collectivités
	</a>
<?php endif;?>



<div class="box">

<form action="Entite/doEdition" method='post'>
	<?php $this->displayCSRFInput(); ?>
<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />

<?php if ($entite_mere) : ?>
<input type='hidden' name='entite_mere' value='<?php echo $entite_mere ?>' />
<?php else: ?>
<input type='hidden' name='entite_mere' value='<?php echo $infoEntite['entite_mere'] ?>' />

<?php endif;?>

<table class='table table-striped'>
	<tr>
	<td class='w300'>Type d'entité</td>
	<td><select name='type'>
	<?php foreach (array(Entite::TYPE_COLLECTIVITE, Entite::TYPE_CENTRE_DE_GESTION) as $type) :?>
		<option value='<?php echo $type?>'
			 <?php echo $infoEntite['type'] == $type?'selected="selected"':''?>> 
		<?php echo Entite::getNom($type) ?> </option>	
		<?php endforeach;?>
		
	</select></td>
	</tr>
<tr>
<th><label for="denomination">Nom<span class='obl'>*</span></label>
<p class='form_commentaire'>60 caractères max</p>
</th>

<td><input type="text" name="denomination" id="denomination" value='<?php echo $infoEntite['denomination']?>'/></td>
</tr>
<tr>
<th><label for="siren">SIREN<span class='obl'>*</span></label>
<p class='form_commentaire'>9 caractères obligatoires </p>
<p class='form_commentaire'>obligatoire pour une collectivité</p></th>
<td>
	<input type="text" name="siren" id="siren" value='<?php echo $infoEntite['siren']?>'/></td>

</tr>

<tr>
	<th><label for="cdg">Centre de gestion</label></th>
	<td>
		<?php $this->render("CDGSelect"); ?>
	</td>
</tr>

</table>

<?php if ($id_e) : ?>
    <button type="submit" class="btn">
        <i class="fa fa-pencil"></i>&nbsp;Modifier
    </button>
<?php else : ?>
	<input type="hidden" name="create" value="true"/>
    <button type="submit" class="btn">
        <i class="fa fa-plus"></i> Créer
    </button>
<?php endif;?>



</form>

</div>
