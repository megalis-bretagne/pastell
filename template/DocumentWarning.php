<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='<?php $this->url("Document/detail?id_d={$id_d}&id_e={$id_e}&page={$page}"); ?>'><i class="fa fa-arrow-left"></i>&nbsp;<?php echo $infoDocument['titre']?></a>

<div class='alert' style='margin-top:10px;'>
	L'action <b><?php echo $actionName ?></b> est irréversible.
</div>



<div class="box">
			<h2>Etes-vous sûr de vouloir effectuer cette action ? </h2>
			
			
			<form action='Document/action' method='post'>
				<?php $this->displayCSRFInput() ?>
				<input type='hidden' name='id_d' value='<?php echo $id_d ?>' />
				<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
				<input type='hidden' name='page' value='<?php echo $page ?>' />			
				<input type='hidden' name='action' value='<?php echo $action ?>' />
				<input type='hidden' name='go' value='1' />
				<input type='submit' class='btn btn-danger' value='<?php echo $actionName?>' />
			</form>
			
</div>