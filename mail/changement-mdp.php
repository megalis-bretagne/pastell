Bonjour, 


Le syst�me Pastell a recu une demande de rappel de mot de passe 
correspondant au compte associ� � votre adresse email.


Afin de r�initialiser votre mot de passe, veuillez vous rendre sur 
la page suivante :

<?php echo SITE_BASE ?>/connexion/changement-mdp.php?mail_verif=<?php echo $info['mail_verif_password']?>

Si vous n'avez pas initi� cette proc�dure, veuillez pr�venir votre administrateur Pastell.

----------
Requ&ecirc;te gen&eacute;r&eacute; par : 

IP : <?php echo $_SERVER['REMOTE_ADDR'] ?>

Date : <?php echo date("Y-m-d h:i:s");?>

Serveur : <?php echo $_SERVER['SERVER_NAME'] ?>

URL: <?php echo $_SERVER['REQUEST_URI'] ?>
------------