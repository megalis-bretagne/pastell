<?php

declare(strict_types=1);

/**
 * @var String $id_u
 * @var Gabarit $this
 */

?>

<a href='Utilisateur/moi' class="btn btn-link"><i class="fa fa-arrow-left"></i>&nbsp;Espace utilisateur</a>

<div class="box">

    <h2>Ajouter un jeton d'authentification</h2>
    <form action='Utilisateur/doAddToken?<?php
    echo 'id_u=' . $id_u;
    if (isset($source)) {
        echo '&source=' . $source;
    } ?>'
    ' method='post'>
    <?php
    $this->displayCSRFInput(); ?>

        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Nom du jeton<span class='obl'>*</span></label>
            <div class="col-md-4">
                <input name="name" id="name" class="form-control" type="text" maxlength="64" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="expiration" class="col-sm-2 col-form-label ">Date d'expiration</label>
            <div class="col-md-4">
                <input type='text'
                       id='expiration'
                       name='expiration'
                       value=''
                       autocomplete="off"
                       class="form-control"
                />
            </div>
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
            </div>
        </div>
        <script type="text/javascript">
            jQuery.datepicker.setDefaults(jQuery.datepicker.regional['fr']);
            $(function () {
                $("#expiration").datepicker({
                    dateFormat: 'yy-mm-dd',
                });
            });
        </script>

        <a class='btn btn-outline-primary' href='Utilisateur/moi'>
            <i class="fa fa-times-circle"></i>&nbsp;Annuler
        </a>

        <button type="submit" class="btn btn-primary">
            <i class="fa fa-floppy-o"></i>&nbsp;Enregistrer
        </button>
    </form>

</div>
