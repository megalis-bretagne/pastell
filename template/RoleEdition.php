<?php
/**
 * @var $this Gabarit
 */
?>
<a class='btn' href='<?php $this->url("Role/index") ?>'>
	<i class="fa fa-arrow-left"></i>&nbsp;Retour à la liste des rôles
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


		<?php if($role_info['role']):?>
            <button type="submit" class="btn">
                <i class="fa fa-pencil"></i>&nbsp;Modifier
            </button>
		<?php else: ?>
            <button type="submit" class="btn">
                <i class="fa fa-plus"></i>&nbsp;Créer
            </button>
		<?php endif; ?>

    </form>




</div>

