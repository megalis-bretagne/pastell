<?php
/** @var Gabarit $this */
/** @var array $extension_info */
?>
<a class='btn btn-mini' href='<?php $this->url("Extension/index")?>'>
	<i class='icon-circle-arrow-left'></i>Liste des extensions
</a>

<div class="box">


<form action='<?php $this->url("Extension/doEdition"); ?>' method='post' >
	<?php $this->displayCSRFInput() ?>
<input type='hidden' name='id_extension' value='<?php hecho($extension_info['id_e'])?>' />
<table class='table table-striped'>
<tr>
	<th class="w200"><label for='login'>
	<label for="path" >Emplacement de l'extension (chemin absolu)</label>
	<span class='obl'>*</span></label> </th>
	<td> <input style='width:500px' type='text' name='path' id="path" value='<?php hecho($extension_info['path'])?>' /></td>
</tr>
</table>
<input type='submit' class='btn' value="<?php echo $extension_info['id_e']?"Modifier":"Ajouter" ?>" />

</form>
</div>