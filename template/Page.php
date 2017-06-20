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
	$nb['Nouveau'] = $nouveau_bouton_url ;
	$nouveau_bouton_url = $nb;
}

if (! headers_sent()) {
	header("Content-type: text/html; charset=utf-8");
}
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

        <?php foreach (array(
                           "jquery-1.11.2.min.js",
                           "jquery-ui.min.js",
                           "htmlentities.js",
                           "jquery.treeview.js",
                           "pastell.js",
                           "jquery.ui.datepicker-fr.js",
                           "zselect.js",
                           "jquery.form.min.js",
                           "bootstrap.min.js"
                       ) as $script) : ?>
            <script type="text/javascript" src="<?php $this->url("js/$script") ?>"></script>
        <?php endforeach; ?>


        <link rel="stylesheet" type="text/css" href="<?php $this->urlWithBuildNumber("img/commun.css")?>" media="screen" />
		<link type="text/css" href="<?php $this->urlWithBuildNumber("img/bs_css/bootstrap.css"); ?>" rel="stylesheet" />
		<link type="text/css" href="<?php $this->urlWithBuildNumber("img/bs_surcharge.css"); ?>" rel="stylesheet" />

		<link rel="stylesheet" href="<?php $this->url("img/jquery.autocomplete.css"); ?>" type="text/css" />
		

		 <!--<link type="text/css" href="<?php $this->url("img/jquery-ui.theme.1.11.2.min.css");?>" rel="stylesheet" />-->
		<link type="text/css" href="<?php $this->url("img/jquery-ui-1.8.10.custom.css"); ?>" rel="stylesheet" />
		<link type="text/css" href="<?php $this->url("img/jquery.treeview.css"); ?>" rel="stylesheet" />

			
	</head>
	<body>
		<div id="global">
			<div id="header">
				<div id="bloc_logo">
					<a href='<?php $this->url() ?>'>
						<img src="<?php echo $this->url("img/commun/logo_pastell.png")?> " alt="Retour à l'accueil" />
					</a>
				</div>
				<?php if ($authentification->isConnected() ) : ?> 
					<div id="bloc_login">
						<img src="<?php $this->url("img/commun/picto_user.png");?>" alt="" class="absmiddle" />
						<strong><a href='<?php $this->url("Utilisateur/moi"); ?>'><?php hecho($authentification->getLogin()) ?></a></strong>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<img src="<?php $this->url("img/commun/picto_logout.png"); ?>" alt="" class="absmiddle" />
						<a href="<?php $this->url("Connexion/logout")?>">Se déconnecter</a>
					</div>
				<?php endif; ?> 
			</div>
			<?php if ($authentification->isConnected() ) : ?>
				<div id="main_menu">				
					<a href="<?php $this->url("/Document/list?type={$type_e_menu}&id_e={$id_e_menu}") ?>" class="picto_flux">Document</a>
					<a href="<?php $this->url("Journal/index?type={$type_e_menu}&id_e={$id_e_menu}") ?>" class="picto_journal">Journal</a>
					<?php if ($roleUtilisateur->hasOneDroit($authentification->getId(),"entite:edition") 
								|| $roleUtilisateur->hasOneDroit($authentification->getId(),"annuaire:edition")
							) : ?>
					<a href="<?php $this->url("Entite/detail?id_e={$id_e_menu}") ?>" class="picto_utilisateurs">Administration</a>
					<?php endif;?>
					<a href="<?php hecho(AIDE_URL) ?>" class="picto_aide">Aide</a>
					<?php if ($roleUtilisateur->hasDroit($authentification->getId(),"system:lecture",0)) : ?>
						<a href="<?php $this->url("Role/index") ?>" class="picto_collectivites" style="float: right;">Configuration</a>
					<?php endif;?>
					<?php if ($roleUtilisateur->hasDroit($authentification->getId(),"system:lecture",0)) : ?>
						<a href="<?php $this->url("Daemon/index") ?>" class='picto_collectivites' style="float: right;">
							<?php if ($daemon_stopped_warning): ?>
								<span class="badge badge-daemon">!</span>
							<?php endif;?>
							<?php if ($nb_job_lock): ?>
                                <span class="badge badge-job-lock"><?php echo $nb_job_lock ?></span>
							<?php endif;?>
						
						Démon Pastell</a>
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
						</div>
						<?php if ($nouveau_bouton_url): ?>
							<div id="bloc_boutons">
								<?php foreach ($nouveau_bouton_url as $label => $url) : ?>
									<a class="btn " href="<?php echo $url ?>">
										<i class="icon-chevron-right"></i>
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
		
		<?php $this->render('Footer')?>

	</body>
</html>