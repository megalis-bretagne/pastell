<div> <!-- ANCIENNEMENT CLASS 700w -->

    <?php
    $certificatConnexion = new CertificatConnexion($sqlQuery);
    $id_u = $certificatConnexion->autoConnect();

    if ($id_u ):
        $utilisateur = new Utilisateur($sqlQuery);
        $utilisateurInfo = $utilisateur->getInfo($id_u);
        ?>
        <div class="alert">
            Votre certificat vous permet de vous connecter automatiquement avec le compte
            <a href='Connexion/autoConnect'><?php echo $utilisateurInfo['login'] ?></a>
        </div>
    <?php endif;?>

	<?php if ($message_connexion) : ?>
        <div class="alert">
			<?php echo nl2br($message_connexion)?>
        </div>
	<?php endif;?>

    <div class="box">
        <img src="connexion_img/logo_pastell.svg" alt="logo pastell" class="img-logo-accueil">
        <h2>Veuillez saisir vos identifiants de connexion</h2>

        <form class="form-horizontal" action='<?php $this->url("Connexion/doConnexion") ?>' method='post'>
			<?php $this->displayCSRFInput() ?>
            <input type="hidden" name="request_uri" value="<?php hecho($request_uri) ?>"/>
            <div class="control-group">
                <label class="control-label" for="login">Identifiant *</label>
                <div class="controls">
                    <input type="text" name="login" id="login" class='noautocomplete' autocomplete="off" placeholder="Identifiant" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="password">Mot de passe *</label>
                <div class="controls">
                    <input type="password" name="password" id="password" placeholder="Mot de passe" />
                </div>

            </div>

            <div class="align_right">
                <button type="submit" class="btn btn-connect"><i class="fa fa-sign-in"></i>&nbsp; Se connecter</button>
            </div>
        </form>
        <div class="align_center">
            <a href="<?php $this->url("Connexion/oublieIdentifiant") ?>">J'ai oublié mes identifiants</a>
        </div>
    </div>


</div>
