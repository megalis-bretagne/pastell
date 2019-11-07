<?php
/** @var Gabarit $this */
?>
<a href='Utilisateur/moi' class="btn btn-link"><i class="fa fa-arrow-left"></i>&nbsp;Espace utilisateur</a>


<div class="box">

<h2>Modifier votre mot de passe</h2>
<form action='Utilisateur/doModifPassword' method='post' >
    <?php $this->displayCSRFInput(); ?>
<table class="table table-striped">

<tr>
<th class="w300">Ancien mot de passe : </th>
<td>
    <!-- <input type='password' name='old_password'/> -->
    <div class="input-group">
      <input id="old_password" type="password" class="form-control col-md-5 ls-box-input" name="old_password"/>
      <div class="input-group-append">
        <span class="input-group-text"><i class="fa fa-eye-slash" onclick="switchInputType('old_password',this)"></i></span>
      </div>
    </div>

</td>
</tr>

<tr>
<th>Nouveau mot de passe : </th>
<td>
    <!-- <input type='password' name='password'/> -->
    <div class="input-group">
      <input id="password1" type="password" class="form-control col-md-5 ls-box-input" name="password"/>
      <div class="input-group-append">
        <span class="input-group-text"><i class="fa fa-eye-slash" onclick="switchInputType('password1',this)"></i></span>
      </div>
    </div>

</td>
</tr>

<tr>
<th>Confirmer le nouveau mot de passe : </th>
<td>
    <div class="input-group">
      <input id="password2" type="password" class="form-control col-md-5 ls-box-input" name="password2"/>
      <div class="input-group-append">
        <span class="input-group-text"><i class="fa fa-eye-slash" onclick="switchInputType('password2',this)"></i></span>
      </div>
    </div>
</td>
</tr>


</table>

        <a class='btn btn-secondary' href='Utilisateur/moi'>
                <i class="fa fa-times-circle"></i>&nbsp;Annuler
        </a>

    <button type="submit" class="btn btn-primary">
        <i class="fa fa-floppy-o"></i>&nbsp;Enregistrer
    </button></form>

</div>
