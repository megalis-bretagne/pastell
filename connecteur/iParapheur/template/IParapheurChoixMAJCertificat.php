<?php
/** @var Gabarit $this */
?>
<a class='btn btn-link' href='Connecteur/edition?id_ce=<?php echo $id_ce?>'>
    <i class="fa fa-arrow-left"></i>&nbsp;Retour au connecteur
</a>


<div class='box'>


    <form action='Connecteur/action' method='post' enctype="multipart/form-data">
		<?php $this->displayCSRFInput(); ?>
        <input type='hidden' name='id_ce' value='<?php echo $id_ce ?>' />
        <input type='hidden' name='action' value='<?php echo $action ?>' />
        <input type='hidden' name='go' value='go' />
        <h2>Sélectionner des connecteurs</h2>

        <table class="table table-striped table-hover">
            <tr>
                <th><input type="checkbox" name="select-all" id="select-all" />Entité</th>
                <th>Libellé connecteur</th>
            </tr>
            <?php foreach($all_connecteur as $connecteur) : ?>
                <tr>
                    <td>

                        <input type='checkbox' name='id_ce_list[]' value='<?php echo $connecteur['id_ce']?>'/>&nbsp;
                        <?php hecho($connecteur['denomination'])?>
                    </td>
                    <td>
                        <a href='Connecteur/edition?id_ce=<?php echo $connecteur['id_ce']?>'><?php hecho($connecteur['libelle'])?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Sélectionner un certificat</h2>


        <table class='table table-striped'>
            <tr>
                <td class='w300'>Certificat utilisateur (format P12)</td>
                <td><input type='file' name='user_certificat'/></td>
            </tr>
            <tr>
                <td class='w300'>Mot de passe du certificat utilisateur</td>
                <td><input type='password' name='user_certificat_password'/></td>
            </tr>
        </table>
        <input type='submit' class='btn btn-primary' value='Remplacer'/>
    </form>

</div>
