<?php
/** @var  $menu_gauche_select */


$configuration_menu = array(
	"Role/index" => "Rôles",
	"Extension/index" => "Extensions"
);

$test_menu = array(
	"System/index" => "Test du système",
	"System/flux" => "Flux disponibles",
	"System/connecteur" => "Connecteurs disponibles",
	"System/definition" => "Définition flux"

)

?>
<div id="main_gauche">
	<h2>Configuration</h2>
	<div class="menu">
		<ul>
			<?php foreach($configuration_menu as $url => $libelle) : ?>
			<li>
				<a class="<?php echo $menu_gauche_select==$url?"actif":"" ?>" href='<?php $this->url($url)?>'><?php echo $libelle ?></a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<h2>Test et définition</h2>
	<div class="menu">
		<ul>
			<?php foreach($test_menu as $url => $libelle) : ?>
				<li>
					<a class="<?php echo $menu_gauche_select==$url?"actif":"" ?> fa fa-chevron-right" href='<?php $this->url($url)?>'><?php echo $libelle ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

</div>
