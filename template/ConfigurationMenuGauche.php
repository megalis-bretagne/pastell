<?php

declare(strict_types=1);

/**
 * @var Gabarit $this
 * @var string $menu_gauche_select
 */

$menu = [
    'Auto-test du système' => [
        'System/index' => 'Test du système',
    ],
    'Configuration' => [
        'System/loginPageConfiguration' => 'Configuration de la page de connexion',
        'Role/index' => 'Rôles',
        'Extension/index' => 'Extensions'
    ],
    'Types de dossier' => [
        'System/flux' => 'Types de dossier disponibles',
        'TypeDossier/list' => 'Types de dossier personnalisés (studio)',
        'System/definition' => 'Définition des types de dossier'
    ],
    'Connecteurs' => [
        'System/connecteur' => 'Connecteurs disponibles',
    ],
];
?>
<div id="main_gauche" class="no-breadcrumb ls-on">

    <?php $i = 0; foreach ($menu as $title => $subMenu) : ?>
    <h3 data-toggle="collapse" data-target="#collapse-<?php echo $i; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $i; ?>"><?php hecho($title) ?></h3>
    <div class="menu collapse <?php hecho(array_key_exists($menu_gauche_select, $subMenu) ? 'show' : ''); ?>" id="collapse-<?php echo $i; ?>">
        <ul>
            <?php foreach ($subMenu as $url => $libelle) : ?>
                <li>
                    <a class="<?php echo $menu_gauche_select === $url ? 'actif' : '' ?>" href='<?php $this->url($url)?>'><?php echo $libelle ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
        <?php $i++;
    endforeach ?>

</div>
