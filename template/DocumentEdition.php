<?php
/** @var Gabarit $this */
?>
<?php if ($info) : ?>
<a class='btn' href='<?php $this->url("Document/detail?id_d=$id_d&id_e=$id_e&page=$page"); ?>'><i class="fa fa-arrow-left"></i>&nbsp;<?php echo $info['titre']? $info['titre']:$info['id_d']?></a>
<?php else : ?>
<a class='btn' href='Document/list?type=<?php echo $type ?>&id_e=<?php echo $id_e?>'><i class="fa fa-arrow-left"></i>&nbsp;Liste des documents <?php echo $documentType->getName($type);  ?></a>
<?php endif;?>


<?php $this->render("DonneesFormulaireEdition"); ?>

