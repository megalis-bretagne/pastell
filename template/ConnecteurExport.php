<?php

/**
 * @var Gabarit $this
 * @var int $id_ce
 */
?>

<div class="box">

    <form action='Connecteur/doExport' method='post'>
        <?php $this->displayCSRFInput() ?>
        <input type='hidden' name='id_ce' value='<?php hecho($id_ce)?>'>
        <table class='table table-striped'>
            <tr>
                <th>
                    <label for='password'>
                        Mot de passe<span class='obl'>*</span>
                    </label>
                </th>
                <td>
                    <div class="input-group">
                        <input id="password" type="password" class="form-control col-md-4 ls-box-input" name="password" value=''/>
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-eye-slash" onclick="switchInputType('password',this)"></i></span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    <label for='password_check'>
                        Mot de passe (vérification)<span class='obl'>*</span>
                    </label>
                </th>
                <td>
                    <div class="input-group">
                        <input id="password_check" type="password" class="form-control col-md-4 ls-box-input" name="password_check" value=''/>
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-eye-slash" onclick="switchInputType('password_check',this)"></i></span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <a class='btn btn-outline-primary' href='Connecteur/edition?id_ce=<?php hecho($id_ce); ?>'>
            <i class="fa fa-times-circle"></i>&nbsp;Annuler
        </a>

        <button type='submit' class='btn btn-primary'><i class="fa fa-download"></i>&nbsp;Récupérer le connecteur</button>

    </form>
</div>
