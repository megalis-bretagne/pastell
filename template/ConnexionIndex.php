<?php
/** @var Gabarit $this */
?>
<div class="w700">

<?php if ($message_connexion) : ?>
<div class="alert">
	<?php echo nl2br($message_connexion)?>
</div>
<?php endif;?>


<?php 

$fluxDefinitionFiles = $objectInstancier->FluxDefinitionFiles;

$certificatConnexion = new CertificatConnexion($sqlQuery);
$id_u = $certificatConnexion->autoConnect();
	
if ($id_u ): 
$utilisateur = new Utilisateur($sqlQuery);
$utilisateurInfo = $utilisateur->getInfo($id_u);
?>
<div class="box">
	<h2>Connexion automatique</h2>

	Votre certificat vous permet de vous connecter automatiquement avec le compte
	<a href='Connexion/autoConnect'><?php echo $utilisateurInfo['login'] ?></a>

</div>
<?php endif;?>

<div class="box">
		<h2>Merci de vous identifier</h2>

		<form class="form-horizontal" action='<?php $this->url("Connexion/doConnexion") ?>' method='post'>
			<?php $this->displayCSRFInput() ?>
            <input type="hidden" name="request_uri" value="<?php hecho($request_uri) ?>"/>
			<div class="control-group">
				<label class="control-label" for="login">Identifiant</label>
				<div class="controls">
					<input type="text" name="login" id="login" class='noautocomplete' autocomplete="off" placeholder="Identifiant" />
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label" for="password">Mot de passe</label>
				<div class="controls">
					<input type="password" name="password" id="password" placeholder="Mot de passe" />
				</div>

			</div>
			
			<div class="align_right">
				<button type="submit" class="btn"><i class="icon-user"></i>Connexion</button>
			</div>
		</form>
		<hr/>
		<div class="align_center">
		<a href="<?php $this->url("Connexion/oublieIdentifiant") ?>">J'ai oublié mes identifiants</a>
		</div>
</div>


</div>
