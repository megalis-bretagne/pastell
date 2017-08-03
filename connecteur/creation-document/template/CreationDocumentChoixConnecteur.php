<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='Connecteur/editionModif?id_ce=<?php echo $id_ce?>'>
    <i class='icon-circle-arrow-left'></i>Retour au connecteur
</a>
<div class="box">

    <form action='<?php $this->url("Connecteur/doExternalData") ?>' method='post'>
        <?php $this->displayCSRFInput();?>
    <input type='hidden' name='id_ce' value='<?php echo $id_ce?>' />
    <input type='hidden' name='field' value='<?php echo $field?>' />
        <table class='table table-striped'>

            <tr>
                <th class='w200'>    <label for="connecteur_recup">Connecteur de récupération</label>
                </th>
                <td>
                    <select name='connecteur_recup' id="connecteur_recup">
                        <?php foreach($recuperation_connecteur_list as $id_ce => $libelle) : ?>
                            <option value='<?php hecho($id_ce) ?>'><?php hecho($libelle)?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>


        <input type='submit' class='btn' value='Sélectionner'/>
    </form>
</div>