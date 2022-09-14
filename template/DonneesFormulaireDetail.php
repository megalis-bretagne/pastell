<?php

/**
 * @var FieldData[] $fieldDataList
 * @var array $inject
 * @var string $recuperation_fichier_url
 * @var DonneesFormulaire $donneesFormulaire
 */

$id_ce = $inject['id_ce'];
$id_d = $inject['id_d'];
$action = $inject['action'];
$id_e = $inject['id_e'];

?>
<?php  if (! $donneesFormulaire->isValidable()) :  ?>
    <div class="alert alert-danger">
        <?php hecho($donneesFormulaire->getLastError()); ?>
    </div>
<?php endif; ?>
    
<table class='table table-striped'>
<?php foreach ($fieldDataList as $num_field => $displayField) :
    if ($displayField->getField()->isEditOnly()) {
        continue;
    }

    ?>      <tr>
            <th class="w300">
                <?php hecho($displayField->getField()->getLibelle()); ?>
            </th>
            <td>
                <?php foreach ($displayField->getValue() as $num => $value) :?>
                        <?php if ($displayField->isURL()) :?>
                            <a href='<?php echo $displayField->getURL($recuperation_fichier_url, $num, $id_e)?>' id="link_<?php echo $num_field?>">
                        <?php endif;?>
                            <?php if ($displayField->getField()->getType() == 'textarea') : ?>
                                <?php
                                    $data = get_hecho($value);
                                    $data = preg_replace('#(https?://[^\s]+)#', '<a href="\1">\1</a>', $data);
                                    echo nl2br($data);
                                ?>
                            <?php else :?>
                            <span><?php hecho($value);?></span>
                            <?php endif;?>
                            <br/>
                        <?php if ($displayField->isURL()) :?>
                            </a>
                        <?php endif;?>
                <?php endforeach;?>
                <?php if ($displayField->isDownloadZipAvailable()) : ?>
                    <br/>
                    <a
                            href="<?php echo isset($download_all_link) ? $download_all_link . "&field=" . $displayField->getField()->getName() : "/DonneesFormulaire/downloadAll?id_e=$id_e&id_d=$id_d&id_ce=$id_ce&field=" . $displayField->getField()->getName() ?>"
                            class="btn btn-primary">
                            <i class="fa fa-download"></i>&nbsp;Télécharger toutes les annexes
                    </a>
                <?php endif;?>

                <?php if ($displayField->getField()->getVisionneuse()) :?>
                    <a
                            id='visionneuse_link_<?php echo $num_field?>'
                            class=' btn btn-primary'
                            href='/DonneesFormulaire/visionneuse?id_e=<?php echo $id_e?>&id_d=<?php hecho($id_d)?>&id_ce=<?php hecho($id_ce); ?>&field=<?php hecho($displayField->getField()->getName()) ?>'
                    >
                        <i class="fa fa-eye"></i>
                        &nbsp;Voir
                    </a>
                    <div class='visionneuse_result'></div>
                    <script>
$(document).ready(function(){
    let visionneuse_link = $('#visionneuse_link_<?php echo $num_field ?>');
    $(".visionneuse_result").hide();

    visionneuse_link.click(function(){
        var link=$(this).attr('href');
        var visionneuse_result = $(this).next(".visionneuse_result");
        visionneuse_result.toggle();
            $.ajax({
                url: link,
                cache: false
            }).done(function( html ) {
                visionneuse_result.html( html );
            });
        return false;
    });
                    <?php if (! $displayField->getField()->displayLink()) : ?>
        $("#link_<?php echo $num_field ?>").hide();
                    <?php endif; ?>
});
                    </script>
                    
                <?php endif;?>
            </td>
        </tr>
<?php endforeach;?>
</table>
