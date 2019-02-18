<?php
/** @var  $menu_gauche_select */


$configuration_menu = array(
	"Role/index" => "Rôles",
	"Extension/index" => "Extensions"
);

$test_menu = array(
	"System/index" => "Test du système",
	"System/connecteur" => "Connecteurs disponibles",
	"System/definition" => "Définition flux"

);

$type_de_dossier = array(
	"System/flux" => "Types de dossier génériques",
	"TypeDossier/list" => "Types de dossier personalisés",
);

?>
<div id="main_gauche">
	<h2>Configuration</h2>
	<div class="menu">
		<ul>
			<?php foreach($configuration_menu as $url => $libelle) : ?>
			<li>
				<a class="<?php echo $menu_gauche_select==$url?"actif":"" ?>" href='<?php $this->url($url)?>'><?php echo $libelle ?><i class="fa fa-chevron-right"></i></a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>


    <h2>Type de dossier</h2>
    <div class="menu">
        <ul>
			<?php foreach($type_de_dossier as $url => $libelle) : ?>
                <li>
                    <a class="<?php echo $menu_gauche_select==$url?"actif":"" ?>" href='<?php $this->url($url)?>'><?php echo $libelle ?><i class="fa fa-chevron-right"></i></a>
                </li>
			<?php endforeach; ?>
        </ul>
    </div>

	<h2>Test et définition</h2>
	<div class="menu">
		<ul>
			<?php foreach($test_menu as $url => $libelle) : ?>
				<li>
					<a class="<?php echo $menu_gauche_select==$url?"actif":"" ?>" href='<?php $this->url($url)?>'><?php echo $libelle ?><i class="fa fa-chevron-right"></i></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

</div>
