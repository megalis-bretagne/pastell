<?php

use Pastell\Service\SimpleTwigRendererExemple;

$simpleTwigRendererExemple = new SimpleTwigRendererExemple();
?>
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
            <?php  foreach ($simpleTwigRendererExemple->getExemple() as $type => $exemple) : ?>
                <tr>
                    <td><?php hecho($type) ?></td>
                    <td><?php hecho($exemple[0]) ?></td>
                    <td><?php hecho($exemple[1]) ?></td>
                </tr>
            <?php endforeach; ?>

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