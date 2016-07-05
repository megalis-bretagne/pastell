<?php
/** @var Gabarit $this */
?>
<?php if ($info) : ?>
<a class='btn btn-mini' href='<?php $this->url("Document/detail?id_d=$id_d&id_e=$id_e&page=$page"); ?>'><i class='icon-circle-arrow-left'></i><?php echo $info['titre']? $info['titre']:$info['id_d']?></a>
<?php else : ?>
<a class='btn btn-mini' href='Document/list?type=<?php echo $type ?>&id_e=<?php echo $id_e?>'><i class='icon-circle-arrow-left'></i>Liste des documents <?php echo $documentType->getName($type);  ?></a>
<?php endif;?>


<?php 
	if ($donneesFormulaire->getFormulaire()->getNbPage() > 1 ) {
?>
		<ul class="nav nav-pills" style="margin-top:10px;">
					<?php foreach ($donneesFormulaire->getFormulaire()->getTab() as $page_num => $name) : ?>
						<li <?php echo ($page_num == $page)?'class="active"':'' ?>>
							<a>
							<?php echo ($page_num + 1) . ". " . $name?>
							</a>
						</li>
					<?php endforeach;?>		
				</ul>
		<?php 
	}
?>

<div class="box">
	<?php $this->render("DonneesFormulaireEdition"); ?>
</div>
