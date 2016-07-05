<?php
/** @var Gabarit $this */
?>
<?php if ($this->RoleUtilisateur->hasDroit($info['id_u'],"entite:lecture",$info['id_e']) && $info['id_e']) : ?>
<a class='btn btn-mini' href='Entite/detail?id_e=<?php echo $info['id_e'] ?>&page=1'><i class='icon-circle-arrow-left'></i>Revenir à <?php echo $infoEntiteDeBase['denomination'] ?></a>
<?php endif; ?>

<div class="box">

<h2>Détail de l'utilisateur <?php echo $info['prenom']." " . $info['nom']?>
<?php if ($utilisateur_edition) : ?>
<a class='btn btn-mini' href="Utilisateur/edition?id_u=<?php echo $id_u?>">Modifier</a>
<?php endif;?>
</h2>

<table class='table table-striped'>

<tr>
<th class='w200'>Login</th>
<td><?php echo $info['login'] ?></td>
</tr>

<tr>
<th>Prénom</th>
<td><?php echo $info['prenom'] ?></td>
</tr>

<tr>
<th>Nom</th>
<td><?php echo $info['nom'] ?></td>
</tr>

<tr>
<th>Email</th>
<td><?php echo $info['email'] ?></td>
</tr>

<tr>
<th>Date d'inscription</th>
<td><?php echo time_iso_to_fr($info['date_inscription']) ?></td>
</tr>


<tr>
<th>Entité de base</th>
<td>
	<a href='Entite/detail?id_e=<?php echo $info['id_e']?>'>
		<?php if ($info['id_e']) : ?>
			<?php echo $denominationEntiteDeBase ?>
		<?php else : ?>
			Utilisateur global
		<?php endif;?>
	</a> 
</td>
</tr>

<?php if ($certificat->isValid()) : ?>
<tr>
<th>Certificat</th>
<td><a href='Utilisateur/certificat?verif_number=<?php echo $certificat->getVerifNumber() ?>'><?php echo $certificat->getFancy() ?></a></td>
</tr>
<?php endif;?>

<?php if ( $this->RoleUtilisateur->hasDroit($authentification->getId(),"journal:lecture",$info['id_e'])) : ?>
	<tr>
		<th>Dernières actions</th>
		<td>
		<a href='Journal/index?id_u=<?php echo $id_u?>'>Dernières actions de <?php echo $info['prenom']." " . $info['nom']?> »</a>
		</td>
	</tr>
<?php endif;?>

</table>
</div>


<div class="box">
<h2>Rôle de l'utilisateur</h2>

<table class='table table-striped'>
<tr>
<th class='w200'>Rôle</th>
<th>Entité</th>
<th>&nbsp;</th>
</tr>

<?php foreach ($this->RoleUtilisateur->getRole($id_u) as $infoRole) : ?>
<tr>
	<td><?php echo $infoRole['role']?></td>
	<td>
		<?php if ($infoRole['id_e']) : ?>
			<a href='Entite/detail?id_e=<?php echo $infoRole['id_e']?>'><?php echo $infoRole['denomination']?></a>
		<?php else : ?>
			Toutes les collectivités 
		<?php endif;?>
	</td> 
	<td>
		<?php if ($utilisateur_edition) : ?>
		<a class='btn btn-mini' href='Utilisateur/supprimeRole?id_u=<?php echo $id_u ?>&role=<?php echo $infoRole['role']?>&id_e=<?php echo $infoRole['id_e']?>'>
			enlever ce rôle
		</a>
		<?php endif; ?>
	</td>
</tr>
<?php endforeach;?>
</table>


<?php if ($utilisateur_edition) :

$roleSQL = new RoleSQL($sqlQuery);
$allRole = $roleSQL->getAllRole();

?>
<h3>Ajouter un rôle</h3>
	
	<form action='Utilisateur/ajoutRole' method='post' class='form-inline'>
		<?php $this->displayCSRFInput(); ?>
		<input type='hidden' name='id_u' value='<?php echo $id_u ?>' />
	
		<select name='role' class='zselect_role'>
			<option value=''>...</option>
			<?php foreach($allRole as $role ): ?>
				<option value='<?php echo $role['role']?>'> <?php echo $role['role'] ?> </option>
			<?php endforeach ; ?>
		</select>
		
		<select name='id_e' class='zselect_entite'>
			<option value='0'>Entité racine</option>
			<?php foreach($arbre as $entiteInfo): ?>
				<option value='<?php echo $entiteInfo['id_e']?>'>
					<?php echo $entiteInfo['denomination']?>
				</option>
			<?php endforeach ; ?>
		</select>
		<button type='submit' class='btn'><i class='icon-plus'></i>Ajouter</button>
	</form>
<?php endif; ?>
</div>

<div class="box">
<h2>Notification de l'utilisateur</h2>
<table class='table table-striped'>
<tr>
<th class='w200'>Entité</th>
<th>Type de document</th>
<th>Action</th>
<th>Type d'envoi</th>
<th>&nbsp;</th>
</tr>

<?php foreach ($notification_list as $infoNotification) : ?>
<tr>
	<td>
		<?php if ($infoNotification['id_e']) : ?>
			<a href='Entite/detail?id_e=<?php echo $infoNotification['id_e']?>'><?php echo $infoNotification['denomination']?></a>
		<?php else : ?>
			Toutes les collectivités 
		<?php endif;?>
	</td> 
	<td>
		<?php if($infoNotification['type']): ?>
			<?php 
			echo $this->DocumentTypeFactory->getFluxDocumentType($infoNotification['type'])->getName() ?>
		<?php else : ?>
			Tous
		<?php endif; ?>
	</td>
	<td>
		<ul>
		<?php foreach($infoNotification['action'] as $action):?>
			<li><?php echo $action?$action:'Toutes' ?></li>
		<?php endforeach;?>
		<li><a href='Utilisateur/notification?id_u=<?php echo $infoNotification['id_u']?>&id_e=<?php echo $infoNotification['id_e']?>&type=<?php echo $infoNotification['type']?>'>Modifier</a></li>
		</ul>
	</td>
	<td>
		<?php echo $infoNotification['daily_digest']?"Résumé journalier":"Envoi à chaque événement"?>
		<br/>
		<form action='Utilisateur/notificationToogleDailyDigest' method='post'>
			<input type='hidden' name='id_n' value='<?php echo $infoNotification['id_n']?>'/>
			<input type='submit' class='btn btn-mini' value='modifier'/>
		</form>
	</td>
	
	<td>
		<?php if ($utilisateur_edition) : ?>
			<a class='btn btn-mini btn-danger' href='Utilisateur/notificationSuppression?id_n=<?php echo $infoNotification['id_n'] ?>'>
				enlever cette notification
			</a>
		<?php endif;?>
	</td>
</tr>
<?php endforeach;?>
</table>
<?php if ($utilisateur_edition) : ?>
<h3>Ajouter une notification</h3>
	<form action='Utilisateur/notificationAjout' method='post' class='form-inline'>
		<?php $this->displayCSRFInput(); ?>
		<input type='hidden' name='id_u' value='<?php echo $id_u ?>' />
		
		<select name='id_e' class='zselect_entite'>
			<option value='0'>Entité racine</option>
			<?php foreach($arbre as $entiteInfo): ?>
				<option value='<?php echo $entiteInfo['id_e']?>'><?php echo $entiteInfo['denomination']?> </option>
			<?php endforeach ; ?>
		</select>
		
		<?php $this->DocumentTypeHTML->displaySelectWithCollectivite($all_module); ?>
		<select name='daily_digest'>
			<option value=''>Envoi à chaque événement</option>
			<option value='1'>Résumé journalier</option>
		</select>	
			
		<button type='submit' class='btn'><i class='icon-plus'></i>Ajouter</button>
	</form>
<?php endif;?>

</div>



