<?php
/** @var  $menu_gauche_select */


$admninistration_menu = array(
	"Entite/detail" => "Informations",
	"Entite/utilisateur" => "Utilisateurs",
	"Entite/connecteur" => "Connecteurs",
	"Entite/flux" => "Associations flux",
);


$donnees_menu = array(
	"Mailsec/annuaire" => "Annuaire (mail sécurisé)",
	"Entite/agents" => "Agents (Actes)",
);


?>

<div id="main_gauche">

	<h2>Administration</h2>
	<div class="menu">
		<ul>
			<?php foreach($admninistration_menu as $url => $libelle) : ?>
				<li>
					<a class="<?php echo $menu_gauche_select==$url?"actif":"" ?>" href='<?php $this->url($url."?id_e=$id_e")?>'><?php echo $libelle ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<h2>Données pour les flux</h2>
	<div class="menu">
		<ul>
			<?php foreach($donnees_menu as $url => $libelle) : ?>
				<li>
					<a class="<?php echo $menu_gauche_select==$url?"actif":"" ?>" href='<?php $this->url($url."?id_e=$id_e")?>'><?php echo $libelle ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

