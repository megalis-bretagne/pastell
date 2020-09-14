<?php

/**
 * @var string $changelog
 */
?>
<div class="box">

    <h2>Information de version</h2>
    <table class='table table-striped'>

        <tr>
            <th class="w140">Version</th>
            <td><?php echo $manifest_info['version']; ?></td>
        </tr>
        <tr>
            <th class="w140">Révision</th>
            <td><?php echo $manifest_info['revision']; ?></td>
        </tr>
        <tr>
            <th class="w140">Date du commit</th>
            <td><?php echo $manifest_info['last_changed_date']; ?></td>
        </tr>
        <?php foreach ($listPack as $pack => $enabled) : ?>
            <tr>
                <td><?php hecho($pack)?></td>
                <td><?php hecho($enabled ? 'Activé' : 'Inactivé')?></td>
            </tr>
        <?php endforeach;?>
    </table>

</div>


<div class="box">
    <h2>Journal des modifications (CHANGELOG)</h2>
    <?php echo $changelog; ?>
</div>