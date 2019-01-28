<?php
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
];

$css_files_list = [
	"vendor/fork-awesome/css/fork-awesome.min.css",
	"components/select2/select2-built.css",
	"components/jquery-ui/themes/cupertino/jquery-ui.min.css",
	"vendor/bootstrap/css/bootstrap.css",
	"img/jquery.autocomplete.css",
	"img/jquery.treeview.css",
	"connexion_img/libriciel.css",
	"connexion_img/bs_surcharge.css",
	"connexion_img/commun.css",
];

?>
<!DOCTYPE html>
<html>
<head>
	<title>Connexion - Pastell</title>

	<meta name="description" content="Pastell est un logiciel de gestion de flux de documents. Les documents peuvent être crées via un système de formulaires configurables. Chaque document suit alors un workflow prédéfini, également configurable." />
	<meta name="keywords" content="Pastell, collectivité territoriale, flux, document, données, logiciel, logiciel libre, open source" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<!-- <base href='https://pastell2.test.libriciel.fr/' /> -->

	<base href='<?php echo SITE_BASE ?>' />

	<link rel="shortcut icon" type="images/x-icon" href="<?php $this->url("favicon.ico"); ?>" />

	<link rel="stylesheet" href="<?php $this->url("vendor/fork-awesome/css/fork-awesome.min.css") ?>">


	<?php foreach ($css_files_list as $css_file): ?>
        <link rel="stylesheet" href="<?php $this->url($css_file); ?>" type="text/css" />
	<?php endforeach; ?>

	<?php foreach ($javascript_files_list as $javascript_file): ?>
        <script type="text/javascript" src="<?php $this->url($javascript_file) ?>"></script>
	<?php endforeach; ?>

</head>

<body class="libriciel-background">
<div id="global">
	<div id="header">
		<div id="bloc_logo">
			<a href='<?php $this->url("/") ?>'>
				<img src="connexion_img/pastell_sphere.svg" alt="Retour à l'accueil"/>
			</a>
		</div>
	</div>

	<ul class="breadcrumb hide-connexion">
		<li class="active">Bienvenue</li>
	</ul>


	<div id="main">

		<div id="main_droite" >
			<div id="bloc_titre_bouton" class="hide-connexion">
				<div id="bloc_h1">
					<h1>Connexion</h1>
				</div>
			</div><!-- fin bloc_titre_bouton -->

			<?php $this->render("LastMessage");?>
			<?php $this->render($template_milieu) ?>

		</div>
	</div>
</div>


<!--    AJOUT FOOTER EN SPRITE    -->
<footer class="navbar-inverse">
	<nav class="container-fluid flex-container">
		<div class="footer-left">
			<ul class="libriciels">
				<li class="asalae">
					<a href="https://www.libriciel.fr/asalae" title="En savoir plus à propos du logiciel as@lae" target="_blank">
						<span class="sr-only">as@lae</span>
					</a>
				</li>

				<li class="i-delibre">
					<a href="https://www.libriciel.fr/i-delibre" title="En savoir plus à propos du logiciel i-delibRE" target="_blank">
						<span class="sr-only">i-delibRE</span>
					</a>
				</li>

				<li class="i-parapheur">
					<a href="https://www.libriciel.fr/i-parapheur" title="En savoir plus à propos du logiciel i-Parapheur" target="_blank">
						<span class="sr-only">i-Parapheur</span>
					</a>
				</li>

				<li class="pastell selected">
					<a href="https://www.libriciel.fr/pastell" title="En savoir plus à propos du logiciel PASTELL" target="_blank">
						<span class="sr-only">PASTELL</span>
					</a>
				</li>

				<li class="s2low">
					<a href="https://www.libriciel.fr/s2low" title="En savoir plus à propos du logiciel S²LOW" target="_blank">
						<span class="sr-only">S²LOW</span>
					</a>
				</li>

				<li class="web-dpo">
					<a href="https://www.libriciel.fr/web-dpo/" title="En savoir plus à propos du logiciel web-DPO" target="_blank">
						<span class="sr-only">web-DPO</span>
					</a>
				</li>

				<li class="web-delib">
					<a href="https://www.libriciel.fr/web-delib" title="En savoir plus à propos du logiciel web-delib" target="_blank">
						<span class="sr-only">web-delib</span>
					</a>
				</li>

				<li class="web-gfc">
					<a href="https://www.libriciel.fr/web-gfc" title="En savoir plus à propos du logiciel web-GFC" target="_blank">
						<span class="sr-only">web-GFC</span>
					</a>
				</li>
			</ul>
		</div>
		<div class="footer-center">
			Pastell <?php echo $manifest_info['version-complete'] ?>
			<span class="copyright">/ &copy; Libriciel SCOP 2010-2018</span>
		</div>
		<div class="footer-right flex-container logo-lb">
			<ul class="scop navbar-right">
				<li class="details">
					<a href="https://www.libriciel.fr/" title="Accéder au site de Libriciel SCOP" target="_blank">
						<span class="sr-only">Libriciel SCOP</span>
					</a>
				</li>
			</ul>
		</div>
	</nav>
</footer>
<script>
    // Calcul de min-height pour IE 11
    if (navigator.userAgent.toLowerCase().indexOf('trident/7.0') !== -1) {
        var ie11MinHeight = function() {
            var vhInner = $( window ).height() - (46+51),
                mainDroite = $('#main_droite').height();
            return vhInner > mainDroite ? vhInner : mainDroite;
        };
        $('#main')
            .css('min-height', 'initial')
            .css('height', ie11MinHeight() + 'px');
        $( window ).resize(function() {
            $('#main')('height', ie11MinHeight() + 'px');
        });
    }
</script>
</body>
</html>
