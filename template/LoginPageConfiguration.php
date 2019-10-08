<?php
/**
 * @var Gabarit $this
 * @var string $login_page_configuration
 */
?>

<ls-lib-login-config
        configuration='<?php hecho($login_page_configuration); ?>'
>

</ls-lib-login-config>

<form
        id='login-page-configurator'
        action='<?php $this->url('System/doLoginPageConfiguration'); ?>'
        method='POST'
>
    <?php $this->getCSRFToken()->displayFormInput() ?>

    <input  id='login-page-configurator-input'  type="hidden" name='login_page_configuration' value=''/>
    <button id='login-page-configurator-submit' type="submit" style="display: none"></button>
</form>

<script>
    document.getElementsByTagName("ls-lib-login-config")[0].addEventListener('save', function (config) {
        console.log(JSON.stringify(config.detail));
        document.getElementById('login-page-configurator-input').value = JSON.stringify(config.detail);
        document.getElementById('login-page-configurator').submit();
    })
</script>