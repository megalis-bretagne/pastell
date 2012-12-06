<?php
class UtilisateurListeHTML {
	
	private $droitEdition;
	private $allRole;

	public function addDroitEdition(){
		$this->droitEdition = true;
	}

	public function addRole($allRole){
		$this->allRole = $allRole;
	}
	
	public function display(array $liste_utilisateur,$id_e,$role_selected='',$descendance='',$link="entite/detail.php",$page=1,$search=false,$offset,$nb_utilisateur){ ?>
		
		<h2>Liste des utilisateurs
		<?php if ($this->droitEdition) : ?>
			<a href="utilisateur/edition.php?id_e=<?php echo $id_e?>" class='btn_add'>
				Nouveau
			</a>
		<?php endif;?>
		<?php echo $role_selected?" - $role_selected":""?>
		</h2>
		
		<div>
			<form action="<?php echo $link?>" method='get'>
				<input type='hidden' name='id_e' value='<?php echo $id_e?>'/>
				<input type='hidden' name='page' value='<?php echo $page?>'/>
			<table class='w500'>
				<tr>
				<td>Afficher les utilisateurs des entit�s filles</td>
				<td><input type='checkbox' name='descendance' <?php echo $descendance?"checked='checked'":""?>/><br/></td>
				</tr>
				<tr>
				<td>R�le</td>
				<td><select name='role'>
				<option value=''>N'importe quel r�le</option>
					<?php foreach($this->allRole as $role ): ?>
						<option value='<?php echo $role['role']?>' <?php echo $role_selected==$role['role']?"selected='selected'":""?>> <?php echo $role['libelle'] ?> </option>
					<?php endforeach ; ?>
					</select>
				</td></tr>
				<tr>
				<td>
				Recherche </td><td><input type='text' name='search' value='<?php echo $search?>'/></td>
				</tr>
				<tr>
				<td></td><td>
				<input type='submit' value='Afficher'/>
				</td></tr>
				</table>
			</form>
			</div>
		<br/>
		<?php suivant_precedent($offset,UtilisateurListe::NB_UTILISATEUR_DISPLAY,$nb_utilisateur,$link."?id_e=$id_e&page=$page&search=$search&descendance=$descendance&role_selected=$role_selected"); ?>
		
		
		<table class='tab_02'>
		<tr>
			<th>Pr�nom Nom</th>
			<th>login</th>
			<th>email</th>
			<th>Role</th>
			<?php if ($descendance) : ?>
				<th>Collectivit� de base</th>
			<?php endif;?>
		</tr>
		
		<?php foreach($liste_utilisateur as $user) : ?>
			<tr>
				<td>
					<a href='utilisateur/detail.php?id_u=<?php echo $user['id_u'] ?>'>
						<?php echo $user['prenom']?> <?php echo $user['nom']?>
					</a>
				</td>
				<td><?php echo $user['login']?></td>
				<td><?php echo $user['email']?></td>
				<td>
					<?php foreach($user['all_role'] as $role): ?>
						<?php echo $role['libelle']?:"Aucun droit"; ?> - 
						<a href='entite/detail.php?id_e=<?php echo $role['id_e']?>'>
						<?php echo $role['denomination']?:"Entit� racine"?>
						</a>
						<br/>
					<?php endforeach;?>
				
				</td>
				<?php if ($descendance) : ?>
					<td><a href='entite/detail.php?id_e=<?php echo $user['id_e']?>'><?php echo $user['denomination']?:"Entit� racine"?></a></td>
				<?php endif;?>
			</tr>
		<?php endforeach; ?>
		
		</table>
		
		<?php
	}
	
}