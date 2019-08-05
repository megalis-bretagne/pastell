<a class='btn btn-link' href='Connecteur/editionModif?id_ce=<?php echo $id_ce?>'>
    <i class="fa fa-arrow-left"></i>&nbsp;Retour au connecteur
</a>
<div class="box">
    <h2>Choisissez un type de dossier puis un état</h2>

    <form action='Connecteur/doExternalData' method='post' enctype="multipart/form-data">
        <?php $this->displayCSRFInput(); ?>
        <input type='hidden' name='id_ce' value='<?php echo $id_ce?>' />
        <input type='hidden' name='field' value='<?php echo $field?>' />
        <input type='hidden' name='go' value='go' />
        <table class='table table-striped'>
            <tr id="tr_type_document">
                <th class='w200'>
                    <label for="document_type">Type de dossier</label>
                </th>
                <td>
                    <select name="document_type" id="document_type" class="form-control col-md-2">
                        <option></option>
                        <?php foreach($list_flux as $flux_id => $flux_info) : ?>
                            <option
                                    value="<?php hecho($flux_id) ?>"
                                    <?php echo ($flux_id == $document_type)?"selected='selected'":"" ?>
                            >
                                <?php hecho($flux_info['nom']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </td>
            </tr>
        </table>

        <button type='submit' class='btn btn-primary' id="valider">
            <i class="fa fa-check"></i>&nbsp;Sélectionner
        </button>

    </form>

</div>
