<?php 

require_once( dirname(__FILE__) . "/../init-admin.php");

require_once( PASTELL_PATH . "/lib/utilisateur/UtilisateurListe.class.php");

$utilisateurListe = new UtilisateurListe($sqlQuery);

$page_title = "Liste des utilisateurs";

include( PASTELL_PATH ."/include/haut.php");
?>



<div class="box_contenu clearfix">

<h2>Utilisateurs</h2>

filtrer par : fournisseurs/collectivit�s



suivant/precedant

<table class="tab_01">
	<tr>
		<th>Nom Pr�nom</th>
		<th>Login</th>
		<th>Email</th>
		<th>V�rifi�</th>
		<th>Entit�</th>
	</tr>
<?php foreach($utilisateurListe->getAll() as $i => $user) : ?>
	<tr class='<?php echo $i%2?'bg_class_gris':'bg_class_blanc'?>'>
		<td><?php echo $user['nom']?>&nbsp;<?php echo $user['prenom']?></td>
		<td><?php echo $user['login']?></td>
		<td><?php echo $user['email']?></td>
		<td>
			<?php echo $user['mail_verifie']?"OUI":"NON" ?>
			<?php if ( ! $user['mail_verifie']) : ?>
				<a href='utilisateur/set-mail-verifier.php?id_u=<?php echo $user['id_u']?>'>Marquer comme v�rifi�</a>
			<?php endif;?>
		</td>
		<td>
			<a href='entite/detail.php?siren=<?php echo $user['siren']?>'><?php echo $user['denomination']?></a>
		</td>
	</tr>
<?php endforeach; ?>
</table>

</div>
<div class='box_info'>
<p>Vous pouvez cr�er un nouvel utilisateur en cliquant sur la collectivit� � laquelle celui-ci sera rattach�</p>
</div>

<?php 
include( PASTELL_PATH ."/include/bas.php");
