<?php

/**
 * @var Gabarit $this
 * @var int $id_ce
 * @var string $action
 * @var string $field
 * @var array $connectors
 */
?>
<a class='btn btn-link' href='Connecteur/edition?id_ce=<?php hecho($id_ce); ?>'>
    <i class="fa fa-arrow-left"></i>&nbsp;Retour au connecteur
</a>

<div class='box'>
    <form action='Connecteur/doExternalData' method='post' enctype="multipart/form-data">
        <?php $this->displayCSRFInput(); ?>
        <input type='hidden' name='id_ce' value='<?php hecho($id_ce); ?>'/>
        <input type='hidden' name='action' value='<?php hecho($action); ?>'/>
        <input type='hidden' name='field' value='<?php hecho($field); ?>'/>
        <input type='hidden' name='go' value='go'/>
        <h2>Selectionner des connecteurs</h2>

        <table class="table table-striped table-hover">
            <tr>
                <th scope="col">
                    <input type="checkbox" name="select-all" id="select-all"/>
                    <label for="select-all">Entité</label>
                </th>
                <th scope="col">Libellé connecteur</th>
            </tr>
            <?php foreach ($connectors as $connecteur) : ?>
                <tr>
                    <td>
                        <input type='checkbox' name='id_ce_list[]' value='<?php hecho($connecteur['id_ce']); ?>'/>&nbsp;
                        <?php hecho($connecteur['denomination']); ?>
                    </td>
                    <td>
                        <a href='Connecteur/edition?id_ce=<?php hecho($connecteur['id_ce']); ?>'><?php hecho($connecteur['libelle']); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Sélectionner un certificat</h2>

        <table class='table table-striped'>
            <tr>
                <td class='w300'>Certificat utilisateur (format P12)</td>
                <td><input type='file' name='user_certificat' class="btn btn-secondary col-md-4"/></td>
            </tr>
            <tr>
                <td class='w300'>Mot de passe du certificat utilisateur</td>
                <td><input type='password' name='user_certificat_password' class="col-md-4"/></td>
            </tr>
        </table>
        <button type="submit" class="btn btn-primary"><i class="fa fa-cogs"></i>&nbsp;Remplacer</button>
    </form>

</div>
