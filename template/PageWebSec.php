<?php 

header("Content-type: text/html; charset=utf-8");	 ?>
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
		<link rel="stylesheet" type="text/css" href="img/commun.css" media="screen" />
		<link type="text/css" href="img/bs_css/bootstrap.css" rel="stylesheet" />
		<link type="text/css" href="img/bs_surcharge.css" rel="stylesheet" />
		<!--[if gte IE 6]>
			<link rel="stylesheet" type="text/css" href="img/style_IE6.css" media="screen" />
		<![endif]-->

		<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>


	</head>
	<body>
		<div id="global">
			<div id="header">
				<div id="bloc_logo">
						<img src="img/commun/logo_pastell.png" />
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
