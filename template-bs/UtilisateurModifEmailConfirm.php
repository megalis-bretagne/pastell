
<a class='btn btn-mini' href='utilisateur/moi.php'><i class='icon-circle-arrow-left'></i>Espace utilisateur</a>

<div class="box">

<?php if($result) : ?>
<div class="alert alert-success">

	Votre email a �t� valid�.
	<br/>
	Votre administrateur doit maintenant valider votre changement d'email.
	<br/>
	Vous serez averti par email.
</div>	

<?php else : ?>
<div class="alert alert-error">
Un probl�me emp�che de satisfaire votre demande.
<br/>
Veuillez recommencer la proc�dure de changement d'email.
</div>	
<?php endif;?>

</div>
