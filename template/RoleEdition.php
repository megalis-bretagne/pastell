<?php

/**
 * @var $this Gabarit
 */
?>



<div class="box">

    <form class="form-horizontal" action='<?php $this->url("Role/doEdition"); ?>' method='post'>
        <?php $this->getCSRFToken()->displayFormInput() ?>
        <input type='hidden' name='nouveau' value='<?php hecho($this->viewParameter['nouveau']); ?>'/>
        <div class="control-group">
            <label class="control-label" for="role">Rôle<span class="obl">*</span></label>
            <div class="controls">
                <input  class="form-control col-md-4" <?php echo $role_info['role'] ? "readonly='readonly'" : "" ?> type='text' name='role' id='role' value='<?php hecho($role_info['role']) ?>' />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="libelle">Libellé<span class="obl">*</span></label>
            <div class="controls">
                <input class="form-control col-md-4" type='text' name='libelle' id='libelle' value='<?php hecho($role_info['libelle']) ?>' />
            </div>

        </div>
        <br/>
        <div class="control-group">
            <a class='btn btn-outline-primary' href='<?php $this->url("Role/detail?role={$role_info['role']}") ?>'>
                <i class="fa fa-times-circle"></i>&nbsp;Annuler
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-floppy-o"></i>&nbsp;Enregistrer
            </button>
        </div>

    </form>




</div>
