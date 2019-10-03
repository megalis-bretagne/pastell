<?php
/**
 * @var Gabarit $this
 * @var string $login_page_configuration
 */
?>

<ls-lib-forgot-password
        visual-configuration='<?php hecho($login_page_configuration); ?>'
        logo="connexion_img/logo_pastell.svg"
        form-action="<?php $this->url('Connexion/doOublieIdentifiant'); ?>"
        one-field-forgot=true
        usernameormail-input-name="login"
>
    <?php $this->displayCSRFInput() ?>
</ls-lib-forgot-password>


<script>
    document.getElementsByTagName("ls-lib-forgot-password")[0].addEventListener('backToLogin', function () {
        window.location = "<?php $this->url("Connexion/connexion") ?>";
    })
</script>