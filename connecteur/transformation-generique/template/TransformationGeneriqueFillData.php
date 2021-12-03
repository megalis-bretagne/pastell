<?php

require_once __DIR__ . "/../TransformationGenerique.class.php";
/**
 * @var $field
 * @var array $transformation_data
 *
 */

?>
<div id='box_signature' class='box'>

    <form action='Connecteur/doExternalData' method='post' id='form_sign'>
        <?php $this->displayCSRFInput(); ?>
        <input type='hidden' name='id_ce' value='<?php echo $id_ce ?>'/>
        <input type='hidden' name='field' value='<?php echo $field ?>'/>
        <table class="table table-striped">
            <?php
            $i = 0;
            foreach ($transformation_data as $element_id => $twig_expression) :
                $i++;
                ?>
                <tr>
                    <th class="w500">
                        <label for="id_element_<?php echo $i; ?>">Identifiant de l'élément</label>
                        <input name='id_element[]' id='id_element_<?php echo $i; ?>' class="form-control col-md-12" type="text" value="<?php hecho($element_id) ?>" />
                    </th>
                    <td>
                        <label for="defintion_<?php echo $i; ?>">Transformation</label>
                        <textarea
                                name='definition[]'
                                id='defintion_<?php echo $i; ?>'
                                cols="80"
                                rows="<?php echo max(5, substr_count($twig_expression, "\n") + 1); ?>"
                                class="form-control col-md-12"><?php hecho($twig_expression); ?></textarea>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>

        <a class='btn btn-secondary'
           href='Connecteur/editionModif?id_ce=<?php echo $id_ce ?>'>
            <i class="fa fa-times-circle"></i>&nbsp;Annuler
        </a>

        <button type="submit" class="btn btn-primary" id="add_button" name="add_button" value="add">
            <i class="fa fa-check"></i>&nbsp;Ajouter un élement
        </button>

        <button type="submit" class="btn btn-primary" id="submit_button" name="submit_button">
            <i class="fa fa-check"></i>&nbsp;Enregistrer
        </button>
    </form>

</div>

<?php $this->render("TwigDocumentation"); ?>


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
                <?php /** @var Field $theField */foreach ($fieldsList as $theField) : ?>
                    <tr>
                        <td><?php hecho($theField->getName()) ?></td>
                        <td><?php hecho($theField->getLibelle()) ?></td>
                        <td><?php hecho($theField->getType()) ?></td>
                        <td><?php hecho($theField->getProperties('commentaire')) ?></td>
                    </tr>
                <?php endforeach ?>

            </table>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-warning">Associer ce connecteur à un seul flux de l'entité pour avoir la liste des éléments disponibles sur ce flux</div>
<?php endif; ?>

<div class="box" >
    <a class="collapse-link" data-toggle="collapse" href="#collapseDataPastell">
        <h2> <i class="fa fa-plus-square"></i>Liste des méta-données communes à tous les types de dossier</h2>
    </a>

    <div class="collapse"   id="collapseDataPastell">


        <table class="table table-striped ">
            <tr>
                <th class="w200">Identifiant</th>
                <th class="">Explication</th>
            </tr>
            <?php foreach (TransformationGenerique::getPastellMetadata() as $id => $commentaire) : ?>
                <tr>
                    <td><?php hecho($id) ?></td>
                    <td><?php hecho($commentaire) ?></td>
                </tr>
            <?php endforeach ?>

        </table>
    </div>
</div>


