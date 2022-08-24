<?php

/**
 * @var Gabarit $this
 * @var array $flux_list
 */
?>
<div class="box">
<table style='width:100%;'>
<tr>
<td class='align_right'>
<?php if ($id_e_mere) : ?>
    <form action='<?php $this->url("Flux/toogleHeritage"); ?>' method='post' >
        <?php $this->displayCSRFInput(); ?>
    <input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
    <input type='hidden' name='flux' value='<?php echo FluxEntiteHeritageSQL::ALL_FLUX?>' />
    <?php if ($all_herited) :?> 
        <em>Tous les types de dossier sont hérités de la mère</em>
        <button type='submit' class='btn btn-primary'><i class='fa fa-minus-circle'></i>&nbsp;Supprimer l'héritage</button>
    <?php else :?>
        <button type='submit' class='btn btn-primary'><i class='fa fa-plus-circle'></i>&nbsp;Faire tout hériter</button>
    <?php endif;?>
</form>
<?php endif;?>
</td>

</tr>
</table>

<table class="table table-striped">
        <tr>
            <th>Type de dossier</th>
            <th>#Id du type de dossier</th>
        </tr>

<?php foreach ($flux_list as $flux_id => $flux_info) : ?>
    <tr>
        <td rowspan='<?php echo $flux_id ?>'>
                <a href="<?php $this->url("Flux/detail?id_e=$id_e&flux=$flux_id")?>">
                    <strong>
                        <?php hecho($flux_info['nom']);?>
                    </strong>
                </a>
            <br/>
        </td>
        <td>
            <?php hecho($flux_id);?>
        </td>
    </tr>
<?php endforeach;?>
</table>
</div>