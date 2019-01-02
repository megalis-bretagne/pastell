<?php
/** @var Gabarit $this */
/** @var array $extension_info */
?>


<div class="box">


<form action='<?php $this->url("Extension/doEdition"); ?>' method='post' >
	<?php $this->displayCSRFInput() ?>
<input type='hidden' name='id_extension' value='<?php hecho($extension_info['id_e'])?>' />
<table class='table table-striped'>
<tr>
	<th class="w400"><label for='path'>
	<label for="path" >Emplacement de l'extension (chemin absolu)</label>
	<span class='obl'>*</span></label> </th>
	<td> <input style='width:500px' type='text' name='path' id="path" value='<?php hecho($extension_info['path'])?>' /></td>
</tr>
</table>

    <a class='btn btn-secondary' href='<?php $this->url("Extension/index")?>'>
        <i class="fa fa-times-circle"></i>&nbsp;Annuler
    </a>
	    <button type="submit" class="btn btn-primary">
            <i class="fa fa-floppy-o"></i>&nbsp;Enregistrer
        </button>


</form>
</div>