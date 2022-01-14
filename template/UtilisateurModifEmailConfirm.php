<a class='btn btn-link' href='Utilisateur/moi'><i class="fa fa-arrow-left"></i>&nbsp;Espace utilisateur</a>

<div class="box">

<?php

if ($result) : ?>
<div class="alert alert-success">

    Votre email a été validé.
    <br/>
    Votre administrateur doit maintenant valider votre changement d'email.
    <br/>
    Vous serez averti par email.
</div>  

<?php else : ?>
<div class="alert alert-danger">
Un problème empêche de satisfaire votre demande.
<br/>
Veuillez recommencer la procédure de changement d'email.
</div>  
<?php endif;?>

</div>
