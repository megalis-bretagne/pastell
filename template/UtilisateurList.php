<div class="box">


	<?php if ($droitEdition) : ?>
        <a href="Utilisateur/edition?id_e=<?php echo $id_e?>" class='btn btn-primary grow'><i class="fa fa-plus-circle"></i>&nbsp;Ajouter</a>
	<?php endif;?>

    <h2>Rechercher un utilisateur</h2>


	<form action="Entite/utilisateur" method='get' class="table-end">
		<input type='hidden' name='id_e' value='<?php echo $id_e?>'/>
		<input type='hidden' name='page' value='1'/>
	<table class='table table-striped'>
		<tr>
		<td class='w300'>Afficher les utilisateurs des entités filles</td>
		<td><input type='checkbox' name='descendance' <?php echo $descendance?"checked='checked'":""?>/><br/></td>
		</tr>
		<tr>
		<td>Rôle</td>
		<td><select name='role' class="form-control col-md-5">
		<option value=''>N'importe quel rôle</option>
			<?php foreach($all_role as $role ): ?>
				<option value='<?php echo $role['role']?>' <?php echo $role_selected==$role['role']?"selected='selected'":""?>> <?php echo $role['libelle'] ?> </option>
			<?php endforeach ; ?>
			</select>
		</td></tr>
		<tr>
		<td>
		Recherche </td><td><input class="form-control col-md-5" type='text' name='search' value='<?php hecho($search)?>' placeholder="Rerchercher par nom, prénom ou login"/></td>
		</tr>
		</table>
        <button type="reset" class="btn btn-secondary">
            <i class="fa fa-undo"></i>&nbsp;Réinitialiser
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-search"></i>&nbsp;Rechercher
        </button>
	</form>

<h2>Liste des utilisateurs - résultats de la recherche</h2>

<a class='btn btn-secondary' href='Entite/exportUtilisateur?id_e=<?php echo $id_e?>&descendance=<?php echo $descendance?>&role_selected=<?php echo $role_selected?>&search=<?php echo $search ?>'><i class='fa fa-download'></i>&nbsp;Exporter au format CSV</a>

<?php $this->SuivantPrecedent($offset,UtilisateurListe::NB_UTILISATEUR_DISPLAY,$nb_utilisateur,"Entite/utilisateur?id_e=$id_e&page=1&search=$search&descendance=$descendance&role_selected=$role_selected"); ?>

<table class='table table-striped'>
<thead>
<tr>
	<th class='w200'>Prénom Nom</th>
	<th>login</th>
	<th>email</th>
	<th>Rôle</th>
	<?php if ($descendance) : ?>
		<th>Collectivité de base</th>
	<?php endif;?>
</tr>
</thead>

<?php foreach($liste_utilisateur as $user) : ?>
	<tr>
		<td>
			<a href='Utilisateur/detail?id_u=<?php echo $user['id_u'] ?>'>
				<?php hecho($user['prenom'])?> <?php hecho($user['nom'])?>
			</a>
		</td>
		<td><a href='Utilisateur/detail?id_u=<?php echo $user['id_u'] ?>'><?php echo $user['login']?></a></td>
		<td><?php hecho($user['email'])?></td>
		<td>
			<?php foreach($user['all_role'] as $role): ?>
				<?php echo $role['libelle']?:"Aucun droit"; ?> -
				<a href='Entite/detail?id_e=<?php echo $role['id_e']?>'>
				<?php echo $role['denomination']?:"Entité racine"?>
				</a>
				<br/>
			<?php endforeach;?>

		</td>
		<?php if ($descendance) : ?>
			<td><a href='Entite/detail?id_e=<?php echo $user['id_e']?>'><?php echo $user['denomination']?:"Entité racine"?></a></td>
		<?php endif;?>
	</tr>
<?php endforeach; ?>

</table>

<?php $this->SuivantPrecedent($offset,UtilisateurListe::NB_UTILISATEUR_DISPLAY,$nb_utilisateur,"Entite/utilisateur?id_e=$id_e&page=1&search=$search&descendance=$descendance&role_selected=$role_selected"); ?>

<a class='btn btn-secondary' href='Entite/exportUtilisateur?id_e=<?php echo $id_e?>&descendance=<?php echo $descendance?>&role_selected=<?php echo $role_selected?>&search=<?php echo $search ?>'><i class='fa fa-download'></i>&nbsp;Exporter au format CSV</a>
</div>
