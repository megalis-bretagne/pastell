<?php
/** @var Gabarit $this */
/** @var array $extension_info */
?>
<a class='btn btn-link' href='<?php $this->url("Extension/index")?>'>
	<i class="fa fa-arrow-left"></i>&nbsp;Liste des extensions
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

	<?php if($extension_info['id_e']):?>
        <button type="submit" class="btn">
            <i class="fa fa-pencil"></i>&nbsp;Modifier
        </button>
	<?php else: ?>
        <button type="submit" class="btn">
            <i class="fa fa-plus-circle"></i>&nbsp;Ajouter
        </button>
	<?php endif; ?>

</form>
</div>