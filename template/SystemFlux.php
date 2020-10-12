<?php

/** @var Gabarit $this */
?>
<div class="box">
<table class='table table-striped'>
<tr>
    <th class="w200">Nom symbolique</th>
    <th class="w200">Libellé</th>
    <th>Module valide</th>
    <th>Restrction Pack</th>
</tr>
<?php foreach ($all_flux as $id_flux => $flux) : ?>
    <tr>
        <td><a href='<?php $this->url("System/fluxDetail?id=$id_flux"); ?>'><?php hecho($id_flux); ?></a></td>
        <td><?php hecho($flux['nom']); ?></td>
        <td>
            <?php if (! $flux['is_valide']) : ?>
                <b><a  href='<?php $this->url("System/fluxDetail?id=$id_flux"); ?>'>Erreur sur le type de dossier !</a></b>
            <?php endif;?>
        </td>
        <td>
            <?php if ($flux['list_restriction_pack']) : ?>
                <?php hecho(implode(", ", $flux['list_restriction_pack'])); ?>
            <?php endif;?>
        </td>
    </tr>
<?php endforeach;?>
</table>
</div>
<?php if (! empty($all_flux_restricted)) : ?>
<div class="box">
    <h2>Types de dossier indisponibles sur la plateforme</h2>
    <table class='table table-striped'>
        <tr>
            <th class="w200">Nom symbolique</th>
            <th class="w200">Libellé</th>
            <th>Restrction Pack</th>
        </tr>
        <?php foreach ($all_flux_restricted as $id_flux => $flux) : ?>
            <tr>
                <td><?php hecho($id_flux); ?></td>
                <td><?php hecho($flux['nom']); ?></td>
                <td>
                    <?php if ($flux['list_restriction_pack']) : ?>
                        <?php hecho(implode(", ", $flux['list_restriction_pack'])); ?>
                    <?php endif;?>
                </td>
            </tr>
        <?php endforeach;?>
    </table>
</div>
<?php endif;?>
