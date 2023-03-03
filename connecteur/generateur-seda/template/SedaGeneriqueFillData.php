<?php

/**
 * @var Gabarit $this
 * @var int $id_ce
 * @var string $field
 * @var array $pastell_to_seda
 * @var string $flux
 * @var Field[] $fieldsList
 */
?>
<div id='box_signature' class='box'>

    <form action='Connecteur/doExternalData' method='post' id='form_sign'>
        <?php $this->displayCSRFInput(); ?>
        <input type='hidden' name='id_ce' value='<?php echo $id_ce ?>'/>
        <input type='hidden' name='field' value='<?php echo $field ?>'/>
        <table class="table table-striped">
            <?php foreach ($pastell_to_seda as $pastell_id => $element_info) : ?>
                <tr>
                    <th class="w500">
                        <label for="<?php hecho($pastell_id) ?>">
                            <?php hecho($element_info['libelle']);  ?>
                        </label>
                        <?php if (! empty($element_info['commentaire'])) : ?>
                            <p class="form_commentaire"><?php
                                echo $this->getHTMLPurifier()->purify($element_info['commentaire']); ?></p>
                        <?php endif; ?>
                    </th>
                    <td>
                        <?php if (! empty($element_info['value'])) : ?>
                            <select id="<?php hecho($pastell_id) ?>"
                                    name="<?php hecho($pastell_id) ?>"
                                    class="form-control col-md-12"
                            >
                                <?php foreach ($element_info['value'] as $value) : ?>
                                    <option <?php if (($data[$pastell_id] ?? '') === $value) {
                                        echo 'selected="selected"';
                                            } ?> value="<?php hecho($value)?>"><?php hecho($value); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else : ?>
                            <textarea
                                    id="<?php hecho($pastell_id) ?>"
                                    name="<?php hecho($pastell_id) ?>"
                                    cols="80"
                                    rows="<?php echo max(1, substr_count($data[$pastell_id] ?? "", "\n") + 1); ?>"
                                    class="form-control col-md-12"
                            ><?php hecho($data[$pastell_id] ?? '')?></textarea>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach ?>

        </table>

        <a class='btn btn-outline-primary'
           href='Connecteur/editionModif?id_ce=<?php echo $id_ce ?>'>
            <i class="fa fa-times-circle"></i>&nbsp;Annuler
        </a>

        <button type="submit" class="btn btn-primary">
            <i class="fa fa-check"></i>&nbsp;Enregistrer
        </button>
    </form>

</div>

<?php if ($flux) : ?>
    <div class="box" >
        <a class="collapse-link" data-toggle="collapse" href="#collapseExample">
            <h2> <i class="fa fa-plus-square"></i>Liste des éléments du flux <b><?php hecho($flux) ?></b> possibles</h2>
        </a>

        <div class="collapse"   id="collapseExample">


            <table class="table table-striped ">
                <tr>
                    <th class="w200">Identifiant</th>
                    <th class="w200">Libellé</th>
                    <th class="w200">Type</th>
                    <th>Commentaire</th>
                </tr>
                <?php foreach ($fieldsList as $theField) : ?>
                    <tr>
                        <td>{{ <?php hecho($theField->getName()) ?> }}</td>
                        <td><?php hecho($theField->getLibelle()) ?></td>
                        <td><?php hecho($theField->getType()) ?></td>
                        <td><?php hecho($theField->getProperties('commentaire')) ?></td>
                    </tr>
                <?php endforeach ?>

            </table>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-warning">
        Associer ce connecteur à un seul flux de l'entité pour avoir la liste des éléments disponibles sur ce flux
    </div>
<?php endif; ?>

<?php include __DIR__ . "/SedaGeneriqueFillTwigExplanation.php" ?>


<script type="text/javascript">
    const text = document.getElementsByTagName("textarea");
    for (let i = 0; i < text.length; i++) {
        text[i].setAttribute("style", "height:" + (text[i].scrollHeight) + "px;overflow-y:hidden;");
        text[i].addEventListener("input", OnInput, false);
    }

    function OnInput() {
        this.style.height = 0;
        this.style.height = (this.scrollHeight) + "px";
    }
</script>
