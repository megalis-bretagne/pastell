<?php
/**
 * @var $this Gabarit
 */
?>
<a class='btn btn-mini' href='<?php $this->url("Role/index") ?>'>
	<i class="icon-circle-arrow-left"></i>Retour à la liste des rôles
</a>


<div class="box">

    <form class="form-horizontal" action='<?php $this->url("Role/doEdition"); ?>' method='post'>
		<?php $this->getCSRFToken()->displayFormInput() ?>
		<div class="control-group">
			<label class="control-label" for="role">Rôle<span class="obl">*</span></label>
			<div class="controls">
				<input <?php echo $role_info['role']?"readonly='readonly'":"" ?> type='text' name='role' id='role' value='<?php hecho($role_info['role']) ?>' />
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label" for="libelle">Libellé<span class="obl">*</span></label>
			<div class="controls">
				<input type='text' name='libelle' id='libelle' value='<?php hecho($role_info['libelle']) ?>' />
			</div>

		</div>
		
		
			<input type='submit' class='btn' value="<?php echo $role_info['role']?"Modifier":"Créer" ?>" />

    </form>




</div>

