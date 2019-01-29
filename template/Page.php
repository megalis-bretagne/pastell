<?php

/** @var $page_title */
/** @var $authentification */
/** @var RoleUtilisateur $roleUtilisateur */
/** @var $daemon_stopped_warning */
/** @var $menu_gauche_template */
/** @var $template_milieu */
/** @var $type_e_menu */
/** @var $id_e_menu */
/** @var $this Gabarit */
if (! isset($nouveau_bouton_url)){
	$nouveau_bouton_url = array();
}
if (! is_array($nouveau_bouton_url)){
	$nb['Créer'] = $nouveau_bouton_url ;
	$nouveau_bouton_url = $nb;
}

if (! headers_sent()) {
	header("Content-type: text/html; charset=utf-8");
}

$javascript_files_list = [
	"components/jquery/jquery.min.js", //Le framework javascript de base
	"components/select2/select2-built.js" , //Utilisé notamment pour le breadcrumbs et certain composant de selection
 	"components/select2/dist/js/i18n/fr.js", //Francisation du précédent
    "components/jquery-ui/jquery-ui.min.js", //Notamment utilisé pour le datepicker
    "vendor/bootstrap/js/bootstrap.bundle.min.js",

	"js/flow.js", //Traitement de l'upload des fichiers
	"js/jquery.treeview.js", //Le treeview de selection de la classification actes ...
	"js/pastell.js",
	"js/css-vars-ponyfill.min.js", //pour IE
	"js/search.js", //pour l'accordeon et l'affichage du titre résultat
	"js/ie-ponyfill.js", //pour IE
	"js/top.js", // retour haut de page
	"js/mdp.js" // visibilité mdp


];

$css_files_list = [
	"vendor/fork-awesome/css/fork-awesome.min.css",
    "components/select2/select2-built.css",
	"components/jquery-ui/themes/cupertino/jquery-ui.min.css",
	"img/commun.css",
	"vendor/bootstrap/css/bootstrap.css",
	"img/bs_surcharge.css",
	"img/jquery.autocomplete.css",
	"img/jquery.treeview.css",
];


?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo($page_title) . " - Pastell"; ?></title>

		<meta name="description" content="Pastell est un logiciel de gestion de flux de documents. Les documents peuvent être crées via un système de formulaires configurables. Chaque document suit alors un workflow prédéfini, également configurable." />
		<meta name="keywords" content="Pastell, collectivité territoriale, flux, document, données, logiciel, logiciel libre, open source" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		<base href='<?php echo SITE_BASE ?>' />

		<link rel="shortcut icon" type="images/x-icon" href="<?php $this->url("favicon.ico"); ?>" />

		<?php foreach ($css_files_list as $css_file): ?>
            <link rel="stylesheet" href="<?php $this->url($css_file); ?>" type="text/css" />
		<?php endforeach; ?>

        <?php foreach ($javascript_files_list as $javascript_file): ?>
            <script type="text/javascript" src="<?php $this->url($javascript_file) ?>"></script>
        <?php endforeach; ?>

    </head>
	<body>

    <div id="global">

			<div id="header">
				<!-- <div id="bloc_logo">
					<a href='<?php $this->url() ?>'>
						<img src="<?php $this->url("img/commun/logo_pastell.png")?> " alt="Retour à l'accueil" />
					</a>
				</div> -->

				<div id="bloc_logo">
					<a href='<?php $this->url() ?>'>
						<div class="logo" alt="Retour à l'accueil"></div>
					</a>
				</div>

				<?php if ($authentification->isConnected() ) : ?>
					<div id="bloc_login">
						<a href='<?php $this->url("Utilisateur/moi"); ?>'><?php hecho($authentification->getLogin()) ?></a>
                        <div class="dropdown">
                            <button class="btn burger-menu dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bars"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a href="<?php hecho(AIDE_URL) ?>" class="new_picto dropdown-item"><i class="fa fa-question"></i>&nbsp;<span>Aide</span></a>
                                <a href="<?php $this->url("/Aide/APropos")  ?>" class="new_picto dropdown-item"><i class="fa fa-question"></i>&nbsp;<span>À propos</span></a>
                                <a href="<?php $this->url("/Aide/RGPD") ?>" class="new_picto dropdown-item"><i class="fa fa-user-secret"></i>&nbsp;<span>RGPD</span></a>
                                <a href="<?php $this->url("Connexion/logout")?>" class="dropdown-item separator"><i class="fa fa-sign-out"></i>&nbsp;<span> Se déconnecter</span></a>
                            </div>
                        </div>

                    </div>
				<?php endif; ?>
			</div>
            <?php if ($authentification->isConnected() ) : ?>
				<div id="main_menu">
                    <a href="<?php $this->url("/Document/list?id_e={$id_e_menu}") ?>" class="new_picto"><i class="fa fa-folder-open"></i>&nbsp;Documents</a>
                    <a href="<?php $this->url("Journal/index?type={$type_e_menu}&id_e={$id_e_menu}") ?>" class="new_picto" id="journal_link"><i class="fa fa-list-alt"></i>&nbsp;Journal des évènements</a>
					<?php if ($roleUtilisateur->hasOneDroit($authentification->getId(),"entite:edition")
								|| $roleUtilisateur->hasOneDroit($authentification->getId(),"annuaire:edition")
							) : ?>
					<a href="<?php $this->url("Entite/detail?id_e={$id_e_menu}") ?>" class="new_picto"><i class="fa fa-wrench"></i>&nbsp;Administration</a>
					<?php endif;?>
					<?php if ($roleUtilisateur->hasDroit($authentification->getId(),"system:lecture",0)) : ?>
						<a href="<?php $this->url("Role/index") ?>" class="new_picto" style="float: right;">
                            <i class="fa fa-puzzle-piece"></i>
                            <span>
                            Administration avancée
                            </span>
                        </a>
					<?php endif;?>
					<?php if ($roleUtilisateur->hasDroit($authentification->getId(),"system:lecture",0)) : ?>
						<a href="<?php $this->url("Daemon/index") ?>" class='new_picto' style="float: right;">


						    <i class="fa fa-clock-o"></i>
							<?php if ($daemon_stopped_warning): ?>
                                <p class="badge badge-daemon">!</p>
							<?php endif;?>
							<?php if ($nb_job_lock): ?>
                                <p class="badge badge-job-lock"><?php echo $nb_job_lock ?></p>
							<?php endif;?>
                            Tâches automatiques</a>
					<?php endif;?>

				</div>
			<?php endif; ?>

			<?php $this->render("Breadcrumb") ?>

			<div id="main">
				<?php if ($authentification->isConnected() ) : ?>
					<?php $this->render($menu_gauche_template); ?>
				<?php endif;?>

				<div id="main_droite" >
					<div id="bloc_titre_bouton">
						<div id="bloc_h1">
						<h1><?php echo($page_title); ?></h1>
						<?php $this->render("InfoSelectionnerEntite");?>
						</div>
						<?php if ($nouveau_bouton_url): ?>
							<div id="bloc_boutons">
								<?php foreach ($nouveau_bouton_url as $label => $url) : ?>
									<a class="btn btn-primary grow" href="<?php echo $url ?>">
										<i class="fa fa-plus"></i>
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
