<?php

/**
 * @var Gabarit $this
 * @var string $template_milieu
 */

if (!headers_sent()) {
    header("Content-type: text/html; charset=utf-8");
}

$javascript_files_list = [
    'node_modules/@libriciel/ls-composants/ls-elements.js',
];

$css_files_list = [
    'node_modules/fork-awesome/css/fork-awesome.min.css',
    'node_modules/@libriciel/ls-bootstrap-4/dist/pa-bootstrap-4.css',
    'node_modules/@libriciel/ls-jquery-password/dist/css/ls-jquery-password.css',
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion - Pastell</title>

    <meta name="description"
          content="Pastell est un logiciel de gestion de flux de documents. Les documents peuvent être crées via un système de formulaires configurables. Chaque document suit alors un workflow prédéfini, également configurable."/>
    <meta name="keywords"
          content="Pastell, collectivité territoriale, flux, document, données, logiciel, logiciel libre, open source"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="chrome=1">

    <base href='<?php echo SITE_BASE ?>'/>

    <link rel="shortcut icon" type="images/x-icon" href="<?php $this->url("favicon.ico"); ?>"/>

    <?php foreach ($css_files_list as $css_file) : ?>
        <link rel="stylesheet" href="<?php $this->url($css_file); ?>" type="text/css"/>
    <?php endforeach; ?>

</head>

<body>

<?php $this->render($template_milieu) ?>

<ls-lib-footer
        class="ls-login-footer"
        application_name="Pastell <?php hecho($manifest_info['version']); ?>"
        active="pastell"
>
</ls-lib-footer>

<?php foreach ($javascript_files_list as $javascript_file) : ?>
    <script type="text/javascript" src="<?php $this->url($javascript_file) ?>"></script>
<?php endforeach; ?>

</body>
</html>
