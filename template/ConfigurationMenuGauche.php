<?php

/**
 * @var Gabarit $this
 * @var string $menu_gauche_select
 */

$configuration_menu = [
    'System/loginPageConfiguration' => 'Configuration de la page de connexion',
    "Role/index" => "Rôles",
    "Extension/index" => "Extensions"
];

$test_menu = [
    "System/index" => "Test du système",
    "System/connecteur" => "Connecteurs disponibles",
    "System/definition" => "Définition des types de dossier"

];

$type_de_dossier = [
    "System/flux" => "Types de dossier",
    "TypeDossier/list" => "Types de dossier (studio)",
];

?>
<div id="main_gauche" class="no-breadcrumb ls-on">
    <h3 data-toggle="collapse" data-target="#collapse-0" aria-expanded="false" aria-controls="collapse-0">Configuration</h3>
    <div class="menu collapse <?php hecho(array_key_exists($menu_gauche_select, $configuration_menu) ? "show" : ""); ?>" id="collapse-0">
        <ul>
            <?php foreach ($configuration_menu as $url => $libelle) : ?>
                <li>
                    <a class="<?php echo $menu_gauche_select == $url ? "actif" : "" ?>" href='<?php $this->url($url)?>'><?php echo $libelle ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <h3 data-toggle="collapse" data-target="#collapse-1" aria-expanded="false" aria-controls="collapse-1">Type de dossier</h3>
    <div class="menu collapse <?php hecho(array_key_exists($menu_gauche_select, $type_de_dossier) ? "show" : ""); ?>" id="collapse-1">
        <ul>
            <?php foreach ($type_de_dossier as $url => $libelle) : ?>
                <li>
                    <a class="<?php echo $menu_gauche_select == $url ? "actif" : "" ?>" href='<?php $this->url($url)?>'><?php echo $libelle ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <h3 data-toggle="collapse" data-target="#collapse-2" aria-expanded="false" aria-controls="collapse-2">Test et définition</h3>
    <div class="menu collapse <?php hecho(array_key_exists($menu_gauche_select, $test_menu) ? "show" : ""); ?>" id="collapse-2">
        <ul>
            <?php foreach ($test_menu as $url => $libelle) : ?>
                <li>
                    <a class="<?php echo $menu_gauche_select == $url ? "actif" : "" ?>" href='<?php $this->url($url)?>'><?php echo $libelle ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

</div>
