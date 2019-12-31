<ls-lib-reset-password
        visual-configuration='<?php hecho($login_page_configuration); ?>'
        logo="connexion_img/logo_pastell.svg"
        form-action="<?php $this->url('Connexion/doModifPassword'); ?>"
        password-input-name="password"
        password-confirm-input-name="password2"
>
    <?php $this->displayCSRFInput() ?>
    <input type='hidden' name='mail_verif_password' value='<?php echo $mail_verif_password?>'/>

</ls-lib-reset-password>

