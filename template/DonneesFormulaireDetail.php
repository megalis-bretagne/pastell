<?php

/**
 * @var FieldData[] $fieldDataList
 * @var array $inject
 * @var string $recuperation_fichier_url
 */

$id_ce = $inject['id_ce'];
$id_d = $inject['id_d'];
$action = $inject['action'];
$id_e = $inject['id_e'];

?>
<?php  if (! $donneesFormulaire->isValidable()) :  ?>
	<div class="alert alert-danger">
		<?php  echo $donneesFormulaire->getLastError(); ?>
	</div>
<?php endif; ?>
	
<table class='table table-striped'>
<?php foreach($fieldDataList as $displayField):

    if ($displayField->getField()->isEditOnly()){
        continue;
    }

?>	    <tr>
			<th class="w300">
				<?php echo $displayField->getField()->getLibelle() ?>
			</th>
			<td>
				<?php foreach($displayField->getValue() as $num => $value) :?>
						<?php if ($displayField->isURL()) :?>
							<a href='<?php echo $displayField->getURL($recuperation_fichier_url, $num,$id_e)?>'>
						<?php endif;?>
							<?php if ($displayField->getField()->getType() == 'textarea') : ?>
								<?php echo nl2br(get_hecho($value)); ?>
							<?php else:?>
							<?php hecho($value);?>
							<?php endif;?>
							<br/>
						<?php if($displayField->isURL()):?>
							</a>
						<?php endif;?>
				<?php endforeach;?>
                <?php if($displayField->isDownloadZipAvailable()) : ?>
                    <br/>
                    <a
                            href="<?php echo isset($download_all_link)?$download_all_link."&field=".$displayField->getField()->getName():"/DonneesFormulaire/downloadAll?id_e=$id_e&id_d=$id_d&id_ce=$id_ce&field=".$displayField->getField()->getName() ?>"
                            class="btn btn-primary">
                            <i class="fa fa-download"></i>&nbsp;Télécharger tous les fichiers
                    </a>

                <?php endif;?>

				<?php if($displayField->getField()->getVisionneuse()):?>
					<a class='visionneuse_link btn btn-primary' href='/DonneesFormulaire/visionneuse?id_e=<?php echo $id_e?>&id_d=<?php hecho($id_d)?>&id_ce=<?php hecho($id_ce); ?>&field=<?php hecho($displayField->getField()->getName()) ?>'>
                        <i class="fa fa-eye"></i>
                        &nbsp;Voir
                    </a>
					<div class='visionneuse_result'></div>
					<script>
$(document).ready(function(){

	$(".visionneuse_result").hide();
	
	$('.visionneuse_link').click(function(){
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


	
});
					</script>
					
				<?php endif;?>
			</td>
		</tr>
<?php endforeach;?>
</table>