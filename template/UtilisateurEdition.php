<?php
/**
 * @var Gabarit $this
 * @var array $infoUtilisateur
 * @var Certificat $certificat
 * @var array $arbre
 */
?>


<div class="box">


<form action='Utilisateur/doEdition' method='post' enctype='multipart/form-data'>
	<?php $this->displayCSRFInput(); ?>

<input type='hidden' name='id_u' value='<?php echo $id_u?>'>
<input type="hidden" name="dont_delete_certificate_if_empty" value="true" />
<table class='table table-striped'>
<tr>
	<th class="w300"><label for='login'>
	Identifiant (login)
	<span class='obl'>*</span></label> </th>
	 <td> <input class="form-control col-md-4" type='text' name='login' value='<?php hecho($infoUtilisateur['login']); ?>' /></td>
</tr>
<tr>
	<th><label for='password'>
	Mot de passe
	<span class='obl'>*</span></label> </th>
	 <td>
		 <div class="input-group">
	 	  <input id="user_password" type="password" class="form-control col-md-4 ls-box-input" name="password" value=''/>
	 	  <div class="input-group-append">
	 	    <span class="input-group-text"><i class="fa fa-eye-slash" onclick="switchInputType('user_password',this)"></i></span>
	 	  </div>
	 	</div>
	 </td>
</tr>
<tr>
	<th><label for='password2'>
	Mot de passe (vérification)
	<span class='obl'>*</span></label> </th>
	 <td>
		 <div class="input-group">
	 	  <input id="user_password_verif" type="password" class="form-control col-md-4 ls-box-input" name="password2" value=''/>
	 	  <div class="input-group-append">
	 	    <span class="input-group-text"><i class="fa fa-eye-slash" onclick="switchInputType('user_password_verif',this)"></i></span>
	 	  </div>
	 	</div>
	 </td>
</tr>
<tr>
	<th><label for='email'>Email<span class='obl'>*</span></label> </th>
	<td><input class="form-control col-md-4" type='text' name='email' value='<?php hecho($infoUtilisateur['email']); ?>'/></td>
</tr>
<tr>
	<th><label for='nom'>Nom<span class='obl'>*</span></label> </th>
	<td><input class="form-control col-md-4" type='text' name='nom' value='<?php hecho($infoUtilisateur['nom']); ?>'/></td>
</tr>
<tr>
	<th><label for='prenom'>Prénom<span class='obl'>*</span></label> </th>
	<td><input class="form-control col-md-4" type='text' name='prenom' value='<?php hecho($infoUtilisateur['prenom']); ?>'/></td>
</tr>
<tr>
	<th><label for='certificat'>Certificat (PEM)</label> </th>
	<td><input class="btn btn-secondary col-md-4" type='file' name='certificat' /><br/>
	<?php if ($certificat->isValid()) : ?>
		<?php  echo $certificat->getFancy()?>&nbsp;-&nbsp;
		<a class='btn btn-mini btn-danger' href="Utilisateur/supprimerCertificat?id_u=<?php echo $id_u ?>" ?>Supprimer</a>
	<?php endif;?>
	</td>
</tr>

<?php
$tabEntite = $roleUtilisateur->getEntite($this->Authentification->getId(),'entite:edition');
$entiteListe = new EntiteListe($sqlQuery);


?>
<tr>
	<th>Entité de base</th>
	<td>
		<select name='id_e' class="form-control col-md-4">
			<option value=''>Entité racine</option>
			<?php foreach($arbre as $entiteInfo): ?>
			<option value='<?php echo $entiteInfo['id_e']?>' <?php echo $entiteInfo['id_e']==$infoUtilisateur['id_e']?"selected='selected'":""?>>
				<?php for($i=0; $i<$entiteInfo['profondeur']; $i++){ echo "&nbsp&nbsp;";}?>
				|_<?php hecho($entiteInfo['denomination']); ?> </option>
			<?php endforeach ; ?>
		</select>
	</td>
</tr>

</table>

	<?php if ($id_u) : ?>
        <a class='btn btn-secondary' href='Utilisateur/detail?id_u=<?php echo $id_u ?>'><i class="fa fa-times-circle"></i>&nbsp;Annuler</a>
	<?php elseif ($id_e) : ?>
        <a class='btn btn-secondary' href='Entite/utilisateur?id_e=<?php echo $id_e ?>'><i class="fa fa-times-circle"></i>&nbsp;Annuler</a>
	<?php else : ?>
        <a class='btn btn-secondary' href='Entite/utilisateur?id_e=<?php echo $id_e ?>'><i class="fa fa-times-circle"></i>&nbsp;Annuler</a>
	<?php endif;?>



    <button type="submit" class="btn btn-primary">
        <i class="fa fa-floppy-o"></i>&nbsp;Enregistrer
    </button>


</form>
</div>
