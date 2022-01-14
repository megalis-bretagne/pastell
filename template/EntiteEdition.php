<?php

/** @var Gabarit $this */
?>



<div class="box">

<form action="Entite/doEdition" method='post'>
    <?php $this->displayCSRFInput(); ?>
<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />

<?php if ($entite_mere) : ?>
<input type='hidden' name='entite_mere' value='<?php echo $entite_mere ?>' />
<?php else : ?>
<input type='hidden' name='entite_mere' value='<?php echo $infoEntite['entite_mere'] ?>' />

<?php endif;?>

<table class='table table-striped'>
    <tr>
    <td class='w300'>Type d'entité</td>
    <td><select name='type' class="form-control col-md-4">
    <?php foreach (array(Entite::TYPE_COLLECTIVITE, Entite::TYPE_CENTRE_DE_GESTION) as $type) :?>
        <option value='<?php echo $type?>'
             <?php echo $infoEntite['type'] == $type ? 'selected="selected"' : ''?>> 
        <?php echo Entite::getNom($type) ?> </option>   
    <?php endforeach;?>

    </select></td>
    </tr>
<tr>
<th><label for="denomination">Nom<span class='obl'>*</span></label>
<p class='form_commentaire'>128 caractères maximum</p>
</th>

<td><input class="form-control col-md-4" type="text" maxlength="128" name="denomination" id="denomination" value='<?php hecho($infoEntite['denomination']) ?>'/></td>
</tr>
<tr>
<th><label for="siren">SIREN<span class='obl'>*</span></label>
<p class='form_commentaire'>9 caractères obligatoires </p>
<p class='form_commentaire'>obligatoire pour une collectivité</p></th>
<td>
    <input class="form-control col-md-4" type="text" maxlength="9" name="siren" id="siren" value='<?php echo $infoEntite['siren']?>'/></td>
</tr>

<tr>
    <th><label for="cdg">Centre de gestion</label></th>
    <td>
        <?php $this->render("CDGSelect"); ?>
    </td>
</tr>

</table>

    <?php if ($id_e) : ?>
        <a class='btn btn-outline-primary' href='Entite/detail?id_e=<?php echo $id_e?>'>
            <i class="fa fa-times-circle"></i>&nbsp;Annuler
        </a>
    <?php elseif ($entite_mere) : ?>
        <a class='btn btn-outline-primary' href='Entite/detail?id_e=<?php echo $infoMere['id_e']?>'>
            <i class="fa fa-times-circle"></i>&nbsp;Annuler
        </a>
    <?php else : ?>
        <a class='btn btn-outline-primary' href='Entite/detail'>
            <i class="fa fa-times-circle"></i>&nbsp;Annuler
        </a>
    <?php endif;?>


    <?php if (! $id_e) : ?>
        <input type="hidden" name="create" value="true"/>
    <?php endif;?>

    <button type="submit" class="btn btn-primary" id="entity-edit">
        <i class="fa fa-floppy-o"></i>&nbsp;Enregistrer
    </button>

</form>

</div>
