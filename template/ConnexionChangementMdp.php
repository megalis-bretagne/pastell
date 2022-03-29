<?php
/**
 * @var int $password_min_entropy
 * @var string $login_page_configuration
 * @var string $mail_verif_password
 */
$javascript_files_list = [
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/@libriciel/ls-jquery-password/dist/js/ls-jquery-password.min.js',
];
?>

<ls-lib-reset-password
        visual-configuration='<?php

        hecho($login_page_configuration); ?>'
        logo="img/commun/pastell-color-grey.svg"
        form-action="<?php $this->url('Connexion/doModifPassword'); ?>"
        password-input-name="password"
        password-confirm-input-name="password2"
>
    <?php $this->displayCSRFInput() ?>
    <input type='hidden' name='mail_verif_password' value='<?php echo $mail_verif_password?>'/>
    <div class="box">
        <div class="alert alert-info">
            Le calcul de la force du mot de passe est basé sur
            <a href="https://www.ssi.gouv.fr/administration/precautions-elementaires/calculer-la-force-dun-mot-de-passe/
" target="_blank">la documentation de l'ANSI</a>
        </div>
    </div>
</ls-lib-reset-password>

<?php foreach ($javascript_files_list as $javascript_file) : ?>
    <script type="text/javascript" src="<?php $this->url($javascript_file) ?>"></script>
<?php endforeach; ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('input[type=password]')
            .lsPasswordStrengthMeter( {
                "className": "ls-password-strength-meter",
                "inputGroupClass": "input-group",
                "inputGroupTag": "div",
                "thresholds": [
                    { "value": 0, "className": "bg-danger" },
                    { "value": <?php hecho(floor($password_min_entropy / 2)); ?>, "className": "bg-warning" },
                    { "value": <?php hecho($password_min_entropy); ?>, "className": "bg-success" }
                ]
            })
            .lsPasswordToggler($.fn.lsPasswordToggler.configure('4.3'));

    });
</script>