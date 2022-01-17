<?php

/**
 * @var Gabarit $this
 * @var string $request_uri
 * @var string $login_page_configuration
 * @var SQLQuery $sqlQuery
 */

?>

<ls-lib-login-form
        visual-configuration='<?php hecho($login_page_configuration); ?>'
        logo="img/commun/pastell-color-grey.svg"
        form-action="<?php $this->url('Connexion/doConnexion') ?>"
        username-input-name="login"
        password-input-name="password"
        <?php if ($this->getLastMessage()->getLastMessage()) :?>
            password-reset = "true"

        <?php elseif ($this->getLastError()->getLastError()) : ?>
            show-error="true"
            error-message="<?php hecho($this->getLastError()->getLastError());?>"
        <?php endif; ?>
>

    <?php
    $certificatConnexion = new CertificatConnexion($sqlQuery);
    $id_u = $certificatConnexion->autoConnect();

    if ($id_u) :
        $utilisateur = new UtilisateurSQL($sqlQuery);
        $utilisateurInfo = $utilisateur->getInfo($id_u);
        ?>
        <div class="alert alert-info">
            Votre certificat vous permet de vous connecter automatiquement avec le compte
            <a href='Connexion/autoConnect'><?php echo $utilisateurInfo['login'] ?></a>
        </div>
    <?php endif;
    ?>
    <input type="hidden" name="request_uri" value="<?php hecho($request_uri) ?>"/>
    <?php $this->displayCSRFInput() ?>
</ls-lib-login-form>


<script>
    document.getElementsByTagName("ls-lib-login-form")[0].addEventListener('forgotPassword', function () {
        window.location = "<?php $this->url('Connexion/oublieIdentifiant'); ?>";
    })
</script>

<noscript>
    <div class="box">
        <h2>Veuillez saisir vos identifiants de connexion</h2>

        <form class="form-horizontal" action='<?php $this->url("Connexion/doConnexion") ?>' method='post'>
            <?php $this->displayCSRFInput() ?>
            <input type="hidden" name="request_uri" value="<?php hecho($request_uri) ?>"/>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label align_right" for="login">Identifiant *</label>
                <div class="col-sm-8">
                    <input type="text" name="login" id="login" class='noautocomplete form-control' autocomplete="off"
                           placeholder="Identifiant"/>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label align_right" for="password">Mot de passe *</label>
                <div class="col-sm-8">
                    <input type="password" name="password" id="password" placeholder="Mot de passe"
                           class="form-control"/>
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
</noscript>
