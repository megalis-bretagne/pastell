<?php
/** @var Gabarit $this */
?>


<div class="box">
<form action='Document/doExternalData' method='post' >
    <?php $this->displayCSRFInput() ?>
    <input type='hidden' name='id_d' value='<?php echo $id_d?>' />
    <input type='hidden' name='id_e' value='<?php echo $id_e?>' />
    <input type='hidden' name='page' value='<?php echo $page?>' />
    <input type='hidden' name='field' value='<?php echo $field?>' />
    <table class='table table-striped'>

        <tr>
            <th class='w200'>    <label for="iparapheur_sous_type">Sous-type i-Parapheur</label>
            </th>
            <td>
                <select name='iparapheur_sous_type' id="iparapheur_sous_type" class="form-control col-md-2">
                    <?php foreach ($sous_type as $num => $type_message) : ?>
                        <option value='<?php hecho($type_message) ?>'><?php hecho($type_message)?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

    </table>

    <a class='btn btn-secondary'
       href='<?php $this->url("Document/edition?id_d=$id_d&id_e=$id_e&page=$page") ?>'><i class="fa fa-times-circle"></i>&nbsp;Annuler</a>


    <button type='submit' class='btn btn-primary' id="valider">
        <i class="fa fa-check"></i>&nbsp;Valider
    </button>
</form>
</div>