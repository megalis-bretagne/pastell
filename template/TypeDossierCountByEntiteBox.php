<?php
/**
 * @var $entite_list array
 * @var $id_type_dossier string
 */
?>
<table class='table table-striped'>
    <tr>
        <th>Entité</th>
        <th>Nombre de documents</th>
    </tr>
    <?php foreach ($entite_list as $entite_info) : ?>
        <tr>
            <td><a href="Document/list?id_e=<?php echo $entite_info['id_e']?>&type=<?php hecho($id_type_dossier) ?>">
                    <?php hecho($entite_info['denomination'])?></a>
            </td>
            <td><?php echo $entite_info['nb_documents']?></td>
        </tr>
    <?php endforeach;?>
</table>