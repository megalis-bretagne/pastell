<?php

/**
 * @var Gabarit $this
 * @var int $id_ce
 * @var string $field
 * @var array $list_etat
 * @var string $document_etat
 */
?>

<a class='btn btn-link' href='Connecteur/editionModif?id_ce=<?php

echo $id_ce?>'>
    <i class="fa fa-arrow-left"></i>&nbsp;Retour au connecteur
</a>
<div class="box">
    <h2>Choisissez un état du dossier</h2>

    <form action='Connecteur/doExternalData' method='post' enctype="multipart/form-data">
        <?php $this->displayCSRFInput(); ?>
        <input type='hidden' name='id_ce' value='<?php echo $id_ce?>' />
        <input type='hidden' name='field' value='<?php echo $field?>' />
        <input type='hidden' name='go' value='go' />
        <table class='table table-striped'>
            <tr id="tr_type_document">
                <th class='w200'>
                    <label for="document_etat">Etat du dossier</label>
                </th>
                <td>
                    <select name="document_etat" id="document_etat" class="form-control col-md-3">
                        <option></option>
                        <?php foreach ($list_etat as $etat_id => $etat_info) : ?>
                            <option
                                value="<?php hecho($etat_id) ?>"
                                <?php echo ($etat_id == $document_etat) ? "selected='selected'" : "" ?>
                            >
                                <?php hecho(isset($etat_info['name']) ? $etat_info['name'] : $etat_id) ?>
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
