<?php 
require_once("../../init.php");

$page_title = "Inscription non termin�e";

include( PASTELL_PATH ."/include/haut.php");

require_once( PASTELL_PATH . "/lib/utilisateur/Utilisateur.class.php");
$utilisateur = new Utilisateur($sqlQuery,$authentification->getId());
$infoUtilisateur = $utilisateur->getInfo();
?>
<div>
<p>Vous devez cliquez sur le lien du mail qui a �t� envoy� � :
 <b><?php echo $infoUtilisateur['email']; ?></b></p>

<p>
Vous pouvez �galement saisir le code qui vous a �t� envoy� dans le mail : 
</p>
<form action='inscription/fournisseur/mail-validation-controler.php' method='get' >
	<input type='text' name='chaine_verif' value='' />
</form>

<br/>

<p>Si ce n'est pas la bonne adresse email, vous pouvez <a href='inscription/fournisseur/desincription.php'>recommencer la proc�dure</a> </p>

<p>Nous pouvons �galement <a href='inscription/fournisseur/renvoie-mail-inscription.php'>renvoyer le mail</a></p>
</div>
<?php 
include( PASTELL_PATH ."/include/bas.php");
