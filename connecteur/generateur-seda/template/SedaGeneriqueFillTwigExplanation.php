<div class="box">
    <a class="collapse-link" data-toggle="collapse" href="#collapse2">
        <h2> <i class="fa fa-plus-square"></i>Commandes Twig</h2>
    </a>

    <div class="collapse alert alert-info"   id="collapse2">
        <p>Tous les élements du bordereau peuvent recevoir une expression <a href="https://twig.symfony.com/" target="_blank">twig</a> qui sera analysé lors de la génération du bordereau </p>
        <table class="table table-striped" >
            <tr>
                <th class="w300">Type de transformation</th>
                <th>Exemple de transformation</th>
                <th>Explication</th>
            </tr>
            <tr>
                <td>Constante</td>
                <td>ACTE20201204AAA</td>
                <td>Sera simplement utilisé tel quel dans le bordereau</td>
            </tr>
            <tr>
                <td>Contenu d'un élement du formulaire</td>
                <td> {{ actes_numero }} </td>
                <td>Sera remplacé par le contenu de l'élément Pastell <em>actes_numero</em></td>
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
        </table>

        <p>L'expression pour les fichiers et les unités d'archivage doit renvoyé un identifiant d'élément Pastell de type fichier</p>
        <p>Si l'expression d'un fichier commence par #ZIP# alors, le fichier sera dézippé, les repertoires seront transformés en unité d'archivage et les fichiers en document (exemple : #ZIP#fichier_zip)</p>

        <table class="table table-striped" >
            <tr>
                <th class="w300">Mot clé des descriptions de fichiers et unités d'archivage</th>
                <th>Explication</th>
                <th>Exemple</th>
            </tr>
            <tr>
                <td>#FILE_NUM#</td>
                <td>Numéro de fichier (commence par 0) dans le cas d'un fichier Pastell multiple.</td>
                <td>Annexe numéro {{ #FILE_NUM# + 1}}</td>
            </tr>
            <tr>
                <td>#FILENAME#</td>
                <td>Dans le cadre d'un fichier ZIP, le nom du fichier (sans le répertoire)</td>
                <td>Fichier #FILENAME#</td>
            </tr>
            <tr>
                <td>#FILEPATH#</td>
                <td>Dans le cadre d'un fichier ZIP, le chemin relatif à la racine du ZIP vers le fichier</td>
                <td>Fichier #FILEPATH#</td>
            </tr>
            <tr>
                <td>#IS_DIR#</td>
                <td>Dans le cadre d'un fichier ZIP, remplacé par "true" sur un répertoire, "false" sinon</td>
                <td>{% if (#IS_DIR#) %}Unité d'archivage #FILENAME#{% endif %}</td>
            </tr>
            <tr>
                <td>#IS_FILE#</td>
                <td>Dans le cadre d'un fichier ZIP, remplacé par "true" sur un fichier régulier (hors répertoire), "false" sinon</td>
                <td>{% if (#IS_FILE#) %}Fichier #FILENAME#{% endif %}</td>
            </tr>
        </table>



    </div>

</div>