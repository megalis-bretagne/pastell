<?php

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
                        <input name='id_element[]' id='id_element_<?php echo $i; ?>' class="form-control col-md-5" type="text" value="<?php hecho($element_id) ?>" />
                    </th>
                    <td>
                        <label for="defintion_<?php echo $i; ?>">Transformation</label>
                        <textarea name='definition[]' id='defintion_<?php echo $i; ?>' cols="80" rows="10" class="form-control col-md-5"><?php hecho($twig_expression); ?></textarea>
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

<div class="box">
    <a class="collapse-link" data-toggle="collapse" href="#collapse2">
        <h2> <i class="fa fa-plus-square"></i>Explications</h2>
    </a>

    <div class="collapse alert alert-info"   id="collapse2">
        <p>Identifiant de l'élément représente l'élément qui va recevoir le résultat de la transformation</p>
        <p>Transformation représente une expression <a href="https://twig.symfony.com/" target="_blank">twig</a> dont le résultat sera affecté à l'élément associé</p>
        <table class="table table-striped" >
            <tr>
                <th class="w300">Type de transformation</th>
                <th>Exemple de transformation</th>
                <th>Explication</th>
            </tr>
            <tr>
                <td>Constante</td>
                <td>exemple de constante</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Contenu d'un élement du formulaire</td>
                <td> {{ actes_numero }} </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Mélange constante et élement du formulaire</td>
                <td> Actes numéro {{ actes_numero }} concernant {{ agent_prenom }} {{ agent_nom }}</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Expression conditionnel</td>
                <td> {% if actes_nature === 4 %}AR38{% else %}AR48{% endif %}</td>
                <td>Si actes_nature est égale à 4, sera remplacé par AR38, sinon AR48</td>
            </tr>
            <tr>
                <td>Expression xpath</td>
                <td> {{ xpath('pes_aller','//EnTetePES/CodBud/@V') }}</td>
                <td>Extrait l'expression xpath (valeur du code budget) à partir du fichier XML identifié par l'élément pes_aller</td>
            </tr>
            <tr>
                <td>Expression jsonpath</td>
                <td> {{ jsonpath('parapheur_metadata','$.metadata1') }}</td>
                <td>Extrait l'expression jsonpath (valeur de metadata1) à partir du fichier JSON identifié par l'élément parapheur_metadata</td>
            </tr>
            <tr>
                <td>Expression csv</td>
                <td> {{ csvpath('data_csv',1,12) }}</td>
                <td>Extrait le contenu de la seconde colonne de la treizième ligne du fichier CSV identifié par l'élement data_csv (les index commencent à 0)</td>
            </tr>
            <tr>
                <td>Expression csv (avec un autre séparateur de champs)</td>
                <td> {{ csvpath('data_csv',1,12,";",'"',"\\") }}</td>
                <td>Idem que l'expression précédente, mais en spécifiant <b>;</b> comme caractère séparateur de colonne (courant avec un tableur en français), " comme clôture de champs et \ comme caractère d'échappement
                    (par défaut on utilise , " et \). <br/>Voir <a href="https://fr.wikipedia.org/wiki/Comma-separated_values">https://fr.wikipedia.org/wiki/Comma-separated_values</a>
                </td>
            </tr>
        </table>
    </div>

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


