<?php
/** @var Gabarit $this */
/** @var array $role_authorized  */
?>

<a class='btn btn-link' href='Entite/utilisateur?id_e=<?php echo $info['id_e']?>'><i class="fa fa-arrow-left"></i>&nbsp;Retour à la liste des utilisateurs</a>


<?php if ($this->RoleUtilisateur->hasDroit($info['id_u'],"entite:lecture",$info['id_e']) && $info['id_e']) : ?>
<a class='btn  btn-link' href='Entite/detail?id_e=<?php echo $info['id_e'] ?>&page=1'><i class="fa fa-arrow-left"></i>&nbsp;Retour à <?php echo $infoEntiteDeBase['denomination'] ?></a>
<?php endif; ?>

<div class="box">

<h2>Détail de l'utilisateur <?php echo $info['prenom']." " . $info['nom']?></h2>

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
	<a href='Entite/detail?id_e=<?php echo $info['id_e']?>' ">
		<?php if ($info['id_e']) : ?>
			<?php echo $denominationEntiteDeBase ?>
		<?php else : ?>
			Entité racine
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
		<a href='Journal/index?id_u=<?php echo $id_u?>' >Dernières actions de <?php echo $info['prenom']." " . $info['nom']?></a>
		</td>
	</tr>
<?php endif;?>

</table>



    <?php if ($utilisateur_edition) : ?>

        <a class='btn btn-primary' href="Utilisateur/edition?id_u=<?php echo $id_u?>">
            <i class="fa fa-pencil"></i>&nbsp;Modifier
        </a>
    <?php endif;?>


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
		<a class='btn btn-danger' href='Utilisateur/supprimeRole?id_u=<?php echo $id_u ?>&role=<?php echo $infoRole['role']?>&id_e=<?php echo $infoRole['id_e']?>'>
            <i class="fa fa-trash"></i>&nbsp;Supprimer
		</a>
		<?php endif; ?>
	</td>
</tr>
<?php endforeach;?>
</table>

<?php if ($utilisateur_edition && $role_authorized) : ?>
	<h3>Ajouter un rôle</h3>
	
	<form action='Utilisateur/ajoutRole' method='post' class='form-inline'>
		<?php $this->displayCSRFInput(); ?>
		<input type='hidden' name='id_u' value='<?php echo $id_u ?>' />
	
		<select name='role' class='select2_role form-control col-md-1'>
			<option value=''>...</option>
			<?php foreach($role_authorized as $role_info ): ?>
				<option value='<?php echo $role_info['role']?>'> <?php echo $role_info['libelle'] ?> </option>
			<?php endforeach ; ?>
		</select>
		
		<select name='id_e' class='select2_entite form-control col-md-1' >
            <option></option>
			<option value='0'>Entité racine</option>
			<?php foreach($arbre as $entiteInfo): ?>
				<option value='<?php echo $entiteInfo['id_e']?>'>
					<?php echo $entiteInfo['denomination']?>
				</option>
			<?php endforeach ; ?>
		</select>
		<button type='submit' class='btn btn-primary'>
                <i class="fa fa-plus-circle"></i>&nbsp;Ajouter
            </button>
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
        </ul>
		<a class="btn btn-primary" href='Utilisateur/notification?id_u=<?php echo $infoNotification['id_u']?>&id_e=<?php echo $infoNotification['id_e']?>&type=<?php echo $infoNotification['type']?>'><i class="fa fa-pencil"></i>&nbsp;Modifier</a>

	</td>
	<td>
		<?php echo $infoNotification['daily_digest']?"Résumé journalier":"Envoi à chaque événement"?>
		<br/>
		<form action='Utilisateur/notificationToogleDailyDigest' method='post'>
			<?php $this->displayCSRFInput(); ?>
			<input type='hidden' name='id_n' value='<?php echo $infoNotification['id_n']?>'/>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-pencil"></i>&nbsp;Modifier
            </button>
		</form>
	</td>
	
	<td>
		<?php if ($utilisateur_edition) : ?>
			<a class='btn btn-danger' href='Utilisateur/notificationSuppression?id_n=<?php echo $infoNotification['id_n'] ?>'>
                <i class="fa fa-trash"></i>&nbsp;Supprimer
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
		
		<select name='id_e' class='select2_entite form-control col-md-1'>
            <option></option>
			<option value='0'>Entité racine</option>
			<?php foreach($arbre as $entiteInfo): ?>
				<option value='<?php echo $entiteInfo['id_e']?>'><?php echo $entiteInfo['denomination']?> </option>
			<?php endforeach ; ?>
		</select>
		
		<?php $this->DocumentTypeHTML->displaySelectWithCollectivite($all_module); ?>
		<select name='daily_digest' class="form-control col-md-1 mr-2">
			<option value=''>Envoi à chaque événement</option>
			<option value='1'>Résumé journalier</option>
		</select>	
			
		<button type='submit' class='btn btn-primary'><i class="fa fa-plus-circle"></i>&nbsp;Ajouter</button>
	</form>
<?php endif;?>

</div>



