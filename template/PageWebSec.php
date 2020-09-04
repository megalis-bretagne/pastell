<?php

/**
 * @var Gabarit $this
 * @var string $page_title
 * @var string $template_milieu
 */

$javascript_files_list = [
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/select2/dist/js/select2.min.js',
    'node_modules/select2/dist/js/i18n/fr.js',
    'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
    'node_modules/components-jqueryui/ui/widgets/datepicker.js',
    'node_modules/components-jqueryui/ui/i18n/datepicker-fr.js',

    "js/flow.js", //Traitement de l'upload des fichiers
    "js/jquery.treeview.js", //Le treeview de selection de la classification actes ...
    "js/pastell.js",
];

$css_files_list = [
    'node_modules/bootstrap/dist/css/bootstrap.min.css',
    'node_modules/fork-awesome/css/fork-awesome.min.css',
    'node_modules/select2/dist/css/select2.min.css',
    'node_modules/components-jqueryui/themes/cupertino/jquery-ui.min.css',

    "img/commun.css",
    'node_modules/@libriciel/ls-bootstrap-4/dist/pa-bootstrap-4.css',
    "img/bs_surcharge.css",
    "img/jquery.autocomplete.css",
    "img/jquery.treeview.css",
];
header_wrapper("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo($page_title) . " - Pastell"; ?></title>
        
        <meta name="description" content="Pastell est un logiciel de gestion de flux de documents. Les documents peuvent être crées via un système de formulaires configurables. Chaque document suit alors un workflow prédéfini, également configurable." />
        <meta name="keywords" content="Pastell, collectivité territoriale, flux, document, données, logiciel, logiciel libre, open source" />
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="chrome=1">
        <base href='<?php echo WEBSEC_BASE ?>' />
        
        <link rel="shortcut icon" type="images/x-icon" href="favicon.ico" />

        <?php foreach ($css_files_list as $css_file) : ?>
            <link rel="stylesheet" href="<?php $this->url_mailsec($css_file); ?>" type="text/css" />
        <?php endforeach; ?>

        <?php foreach ($javascript_files_list as $javascript_file) : ?>
            <script type="text/javascript" src="<?php $this->url_mailsec($javascript_file) ?>"></script>
        <?php endforeach; ?>


    </head>
    <body>
        <div id="global">
            <div id="header">
                <div id="bloc_logo">
                        <div class="logo"></div>
                </div>
            </div>
            <div id="breadcrumb">
            </div>
        
        
            <div id="main" class="clearfix">    
            
                    
                <div id="main_droite" >
                    <div id="bloc_titre_bouton">
                        <div id="bloc_h1">
                        <h1><?php echo($page_title); ?></h1>
                        </div>
                
                    </div><!-- fin bloc_titre_bouton -->

                    <?php $this->render("LastMessage");?>
                    <?php $this->render($template_milieu);?>

                </div>
            </div>
        </div>
        <?php $this->render('Footer')?>
    </body>
</html>
<?php
