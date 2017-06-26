<?php
	/** @var Gabarit $this */
?>

<a class='btn btn-mini' href='Connecteur/editionModif?id_ce=<?php echo $id_ce?>'>
    <i class='icon-circle-arrow-left'></i>Revenir au connecteur
</a>
<div class="box">
    <form action='Connecteur/doExternalData' method='post'>
        <?php $this->displayCSRFInput();?>
        <input type='hidden' name='id_ce' value='<?php echo $id_ce?>' />
        <input type='hidden' name='field' value='<?php echo $field?>' />
        <table class='table table-striped'>

            <tr>
                <th class='w200'>    <label for="iparapheur_sous_type">Type i-Parapheur</label>
                </th>
                <td>
                    <select name='iparapheur_type'>
                        <?php foreach($type_iparapheur as $num => $type_message) : ?>
                            <option value='<?php hecho($type_message) ?>'><?php hecho($type_message)?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

        <input type='submit' class='btn' value='Sélectionner'/>
    </form>
</div>