<?php

/**
 * @var Gabarit $this
 * @var string $request_uri
 * @var string $login_page_configuration
 */

?>

<ls-lib-login-form
        visual-configuration='<?php hecho($login_page_configuration); ?>'
        logo="connexion_img/logo_pastell.svg"
        form-action="<?php $this->url('Connexion/doConnexion') ?>"
        username-input-name="login"
        password-input-name="password"
>

    <?php
    $certificatConnexion = new CertificatConnexion($sqlQuery);
    $id_u = $certificatConnexion->autoConnect();

    if ($id_u):
        $utilisateur = new Utilisateur($sqlQuery);
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
