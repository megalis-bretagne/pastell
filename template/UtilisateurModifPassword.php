<?php
/** @var Gabarit $this */
?>
<a href='Utilisateur/moi' class="btn"><i class="icon-circle-arrow-left"></i>Espace utilisateur</a>


<div class="box">

<h2>Modifier votre mot de passe</h2>
<form action='Utilisateur/doModifPassword' method='post' >
	<?php $this->displayCSRFInput(); ?>
<table class="table table-striped">

<tr>
<th class="w300">Ancien mot de passe : </th>
<td><input type='password' name='old_password'/></td>
</tr>

<tr>
<th>Nouveau mot de passe : </th>
<td><input type='password' name='password'/></td>
</tr>

<tr>
<th>Confirmer le nouveau mot de passe : </th>
<td><input type='password' name='password2'/></td>
</tr>


</table>

    <button type="submit" class="btn">
        <i class="fa fa-pencil"></i>&nbsp;Modifier
    </button></form>

</div>
