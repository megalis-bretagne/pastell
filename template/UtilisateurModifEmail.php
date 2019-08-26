<?php
/** @var Gabarit $this */
?>
<a class='btn btn-link' href='Utilisateur/moi'><i class="fa fa-arrow-left"></i>&nbsp;Espace utilisateur</a>


<div class="box">

<h2>Modifier votre email</h2>
<form action='Utilisateur/modifEmailControler' method='post' >
	<?php $this->displayCSRFInput(); ?>
<table class='table table-striped'>


<tr>
<th class='w200'>Email actuel: </th>
<td><?php hecho($utilisateur_info['email'])?></td>
</tr>


<tr>
<th>Nouvel email : </th>
<td><input type='text' name='email' value='<?php echo $this->LastError->getLastInput('email')?>'/></td>
</tr>

<tr>
<th>Votre mot de passe : </th>
<td><input type='password' name='password' /></td>
</tr>

</table>

    <button type="submit" class="btn btn-primary">
        <i class="fa fa-pencil"></i>&nbsp;Modifier
    </button></form>

</div>
