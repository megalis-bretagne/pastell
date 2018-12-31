<?php
/** @var Gabarit $this */
?>
<a class='btn btn-link' href='<?php $this->url("System/connecteur")?>'>
    <i class="fa fa-arrow-left"></i>&nbsp;Retour à la liste des connecteur
</a>


<div class="box">
    <h2>Description</h2>
    <?php if ($description) :?>
        <?php echo nl2br($description)?>
    <?php else: ?>
        <div class='alert'>Il n'y a pas de description pour ce flux.</div>
    <?php endif;?>
</div>


<div class="box">
    <h2>Élements du formulaire</h2>
    <table class='table table-striped'>
        <tr>
            <th>Id</th>
            <th>Libellé</th>
            <th>Commentaire</th>
        </tr>
        <?php foreach($formulaire_fields as $field_id => $fields_properties) : ?>
            <tr>
                <td><?php hecho($field_id)?></td>
                <td><?php hecho($fields_properties['name'])?></td>
                <td><?php hecho(isset($fields_properties['commentaire'])?$fields_properties['commentaire']:"")?></td>
            </tr>
        <?php endforeach;?>
    </table>
</div>


<div class="box">
    <h2>Action du connecteur </h2>
    <table class='table table-striped'>
        <tr>
            <th>Id</th>
            <th>Nom de l'action</th>
            <th>Nom de l'état</th>
            <th>Classe</th>
            <th>Action automatique</th>
            <th>Emplacement</th>
        </tr>
        <?php
        foreach($all_action as $i => $action) : ?>
            <tr>
                <td><?php hecho($action['id'])?></td>
                <td>
                    <?php if($action['do_name'] != $action['name']) :?>
                        <?php hecho($action['do_name'])?>
                    <?php else: ?>
                        &nbsp;
                    <?php endif;?>

                </td>
                <td><?php hecho($action['name'])?></td>
                <td><?php hecho($action['class'])?></td>
                <td><?php hecho($action['action_auto'])?></td>
                <td><?php hecho($action['path'])?></td>
            </tr>
        <?php endforeach;?>
    </table>
</div>