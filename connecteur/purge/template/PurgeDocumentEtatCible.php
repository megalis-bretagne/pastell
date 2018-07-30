
<a class='btn btn-mini'
   href='<?php $this->url("Connecteur/editionModif?id_ce=$id_ce") ?>'><i class='icon-circle-arrow-left'></i>Retour</a>


<div class="box">
    <h2>Choisissez une action a effectuer sur le document</h2>

    <form action='Connecteur/doExternalData' method='post' enctype="multipart/form-data">
        <?php $this->displayCSRFInput(); ?>
        <input type='hidden' name='id_ce' value='<?php echo $id_ce?>' />
        <input type='hidden' name='field' value='<?php echo $field?>' />
        <input type='hidden' name='go' value='go' />
        <table class='table table-striped'>
            <tr id="tr_type_document">
                <th class='w200'>
                    <label for="document_etat">Action sur le document</label>
                </th>
                <td>
                    <select name="document_etat_cible" id="document_etat_cible" class="w300">
                        <option></option>
                        <?php foreach($list_etat as $etat_id => $etat_info) : ?>
                            <option
                                value="<?php hecho($etat_id) ?>"
                                <?php echo ($etat_id == $document_etat)?"selected='selected'":"" ?>
                            >
                                <?php hecho(isset($etat_info['name-action'])?$etat_info['name-action']:$etat_id) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </td>
            </tr>
        </table>

        <input type='submit' class='btn' value='Sélectionner'/>

    </form>

</div>
