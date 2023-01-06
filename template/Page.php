<?php

/**
 * @var Gabarit $this
 * @var string $page_title
 * @var Authentification $authentification
 * @var RoleUtilisateur $roleUtilisateur
 * @var bool $daemon_stopped_warning
 * @var int $nb_job_lock
 * @var string $menu_gauche_template
 * @var string $template_milieu
 * @var string $type_e_menu
 * @var int $id_e_menu
 */
if (! isset($nouveau_bouton_url)) {
    $nouveau_bouton_url = array();
}
if (! is_array($nouveau_bouton_url)) {
    $nb['Créer'] = $nouveau_bouton_url ;
    $nouveau_bouton_url = $nb;
}

if (! headers_sent()) {
    header("Content-type: text/html; charset=utf-8");
}

$javascript_files_list = [
    'node_modules/jquery/dist/jquery.min.js',
    'node_modules/select2/dist/js/select2.min.js',
    'node_modules/select2/dist/js/i18n/fr.js',
    'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
    'node_modules/components-jqueryui/jquery-ui.js',
    'node_modules/components-jqueryui/ui/widgets/datepicker.js',
    'node_modules/components-jqueryui/ui/i18n/datepicker-fr.js',

    'node_modules/@libriciel/ls-composants/ls-elements.js',
    'node_modules/@libriciel/ls-jquery-password/dist/js/ls-jquery-password.min.js',

    "js/flow.js", //Traitement de l'upload des fichiers
    "js/jquery.treeview.js", //Le treeview de selection de la classification actes ...
    "js/pastell.js",
    "js/css-vars-ponyfill.min.js", //pour IE
    "js/search.js", //pour l'accordeon et l'affichage du titre résultat
    "js/ie-ponyfill.js", //pour IE
    "js/top.js", // retour haut de page
    "js/mdp.js", // visibilité mdp
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
    'node_modules/@libriciel/ls-jquery-password/dist/css/ls-jquery-password.css',
];

?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php hecho($page_title) . " - Pastell"; ?></title>

        <meta name="description" content="Pastell est un logiciel de gestion de flux de documents. Les documents peuvent être crées via un système de formulaires configurables. Chaque document suit alors un workflow prédéfini, également configurable." />
        <meta name="keywords" content="Pastell, collectivité territoriale, flux, document, données, logiciel, logiciel libre, open source" />
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="chrome=1">
        <base href='<?php echo SITE_BASE ?>' />

        <link rel="shortcut icon" type="images/x-icon" href="<?php $this->url("favicon.ico"); ?>" />

        <?php foreach ($css_files_list as $css_file) : ?>
            <link rel="stylesheet" href="<?php $this->url($css_file); ?>" type="text/css" />
        <?php endforeach; ?>

        <?php foreach ($javascript_files_list as $javascript_file) : ?>
            <script type="text/javascript" src="<?php $this->url($javascript_file) ?>"></script>
        <?php endforeach; ?>

    </head>
    <body>

    <div id="global">

            <div id="header">

                <div id="bloc_logo">
                    <a href='<?php $this->url() ?>'>
                        <div class="logo" alt="Retour à l'accueil"></div>
                    </a>
                </div>

                <?php if ($authentification->isConnected()) : ?>
                    <div id="bloc_login">
                        <a href='<?php $this->url("Utilisateur/moi"); ?>'><?php hecho($authentification->getLogin()) ?></a>
                        <div class="dropdown">
                            <button class="btn burger-menu dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bars"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a href="<?php hecho(AIDE_URL) ?>" class="new_picto dropdown-item"><i class="fa fa-question"></i>&nbsp;<span>Aide</span></a>
                                <a href="<?php $this->url("/Aide/APropos")  ?>" class="new_picto dropdown-item"><i class="fa fa-info"></i>&nbsp;<span>À propos</span></a>
                                <a href="<?php $this->url("/Aide/RGPD") ?>" class="new_picto dropdown-item"><i class="fa fa-user-secret"></i>&nbsp;<span>RGPD</span></a>
                                <a href="<?php $this->url("Connexion/logout")?>" class="dropdown-item separator"><i class="fa fa-sign-out"></i>&nbsp;<span> Se déconnecter</span></a>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>
            </div>
            <?php if ($authentification->isConnected()) : ?>
                <div id="main_menu">
                    <a href="<?php $this->url("/Document/list?id_e={$id_e_menu}") ?>" class="new_picto"><i class="fa fa-folder-open"></i>&nbsp;Dossiers</a>
                    <a href="<?php $this->url("Journal/index?type={$type_e_menu}&id_e={$id_e_menu}") ?>" class="new_picto" id="journal_link"><i class="fa fa-list-alt"></i>&nbsp;Journal des évènements</a>
                    <?php if (
                    $roleUtilisateur->hasOneDroit($authentification->getId(), "entite:edition")
                                || $roleUtilisateur->hasOneDroit($authentification->getId(), "annuaire:edition")
) : ?>
                    <a href="<?php $this->url("Entite/detail?id_e={$id_e_menu}") ?>" class="new_picto"><i class="fa fa-wrench"></i>&nbsp;Administration</a>
                    <?php endif;?>
                    <?php if ($roleUtilisateur->hasDroit($authentification->getId(), "system:lecture", 0)) : ?>
                        <a href="<?php $this->url('System/loginPageConfiguration') ?>" class="new_picto" style="float: right;">
                            <i class="fa fa-puzzle-piece"></i>
                            <span>
                            Administration avancée
                            </span>
                        </a>
                    <?php endif;?>
                    <?php if ($roleUtilisateur->hasDroit($authentification->getId(), "system:lecture", 0)) : ?>
                        <a href="<?php $this->url("Daemon/index") ?>" class='new_picto' style="float: right;">

                            <?php if ($daemon_stopped_warning) : ?>
                                <p class="badge badge-daemon">!</p>
                            <?php endif;?>
                            <?php if ($nb_job_lock) : ?>
                                <p class="badge badge-job-lock"><?php echo $nb_job_lock ?></p>
                            <?php endif;?>
                            <i class="fa fa-clock-o"></i>

                            Tâches automatiques</a>
                    <?php endif;?>

                </div>
            <?php endif; ?>

            <?php if (empty($dont_display_breacrumbs)) : ?>
                <?php $this->render("Breadcrumb") ?>
            <?php endif; ?>

            <div id="main">
                <?php if ($authentification->isConnected() && empty($pages_without_left_menu)) : ?>
                    <?php $this->render($menu_gauche_template); ?>
                <?php endif;?>

                <div id="main_droite" <?php if (! empty($pages_without_left_menu)) :
                    ?>class="pa-one-block"<?php
                                      endif;?>>
                    <div id="bloc_titre_bouton">
                        <div id="bloc_h1">
                        <h1><?php hecho($page_title); ?></h1>
                        <?php $this->render("InfoSelectionnerEntite");?>
                        </div>
                        <?php if ($nouveau_bouton_url) : ?>
                            <div id="bloc_boutons">
                                <?php foreach ($nouveau_bouton_url as $label => $url) : ?>
                                    <a class="btn btn-primary grow" href="<?php echo $url ?>">
                                        <i class="fa <?php echo $label == "Ajouter" ? 'fa-plus-circle' : 'fa-plus'?>"></i>
                                        <?php echo $label?>
                                    </a>
                                <?php endforeach;?>
                            </div>
                        <?php endif;?>
                    </div><!-- fin bloc_titre_bouton -->

                    <?php $this->render("LastMessage");?>
                    <?php $this->render($template_milieu);?>

                </div>
            </div>
        </div>
    <script>
        $(document).ready(function() {
            $('.dropdown-toggle').dropdown()

        });
    </script>

        <?php $this->render('ToTheTop')?>

    <?php $this->render('Footer')?>

    </body>
</html>
