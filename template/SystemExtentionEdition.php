<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='<?php $this->url("System/index?page_number={$this->SystemControler->getPageNumber('extensions')}")?>'>
	<i class='icon-circle-arrow-left'></i>Liste des extensions
</a>

<div class="box">


<form action='<?php $this->url("System/doExtensionEdition"); ?>' method='post' >
	<?php $this->displayCSRFInput() ?>
<input type='hidden' name='id_extension' value='<?php hecho($extension_info['id_e'])?>' />
<table class='table table-striped'>
<tr>
	<th class="w200"><label for='login'>
	Emplacement de l'extension (chemin absolu)
	<span class='obl'>*</span></label> </th>
	<td> <input style='width:500px' type='text' name='path' value='<?php hecho($extension_info['path'])?>' /></td>
</tr>
</table>
<input type='submit' class='btn' value="<?php echo $extension_info['id_e']?"Modifier":"Ajouter" ?>" />

</form>
</div>