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

		<a class='btn btn-secondary' href='Utilisateur/moi'>
				<i class="fa fa-times-circle"></i>&nbsp;Annuler
		</a>

    <button type="submit" class="btn btn-primary">
        <i class="fa fa-floppy-o"></i>&nbsp;Enregistrer
    </button></form>

</div>
