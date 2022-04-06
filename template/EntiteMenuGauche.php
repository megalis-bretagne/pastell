<?php

/**
 * @var Gabarit $this
 * @var $menu_gauche_select
 * @var $droit_lecture_on_connecteur
 */


$admninistration_menu = [
    "Entite/detail" => "Informations (entités)",
    "Entite/utilisateur" => "Utilisateurs",
];


if ($droit_lecture_on_connecteur) {
    $admninistration_menu["Entite/connecteur"] = "Connecteurs" . ($id_e ? "" : " globaux");
    $admninistration_menu["Flux/index"] = $id_e ? "Types de dossier (association)" : 'Associations connecteurs globaux';
}

$donnees_menu = [
    "MailSec/annuaire" => "Annuaire (mail sécurisé)",
    "Entite/agents" => "Agents (Actes)",
];


?>

<div id="main_gauche" class="ls-on">

    <h3 data-toggle="collapse" data-target="#collapse-0" aria-expanded="false" aria-controls="collapse-0">Administration</h3>
    <div class="menu collapse <?php hecho(array_key_exists($menu_gauche_select, $admninistration_menu) ? "show" : ""); ?>" id="collapse-0">
        <ul>
            <?php foreach ($admninistration_menu as $url => $libelle) : ?>
                <li>
                    <a class="<?php echo $menu_gauche_select == $url ? "actif" : "" ?>" href='<?php $this->url(get_hecho($url . "?id_e=$id_e")); ?>'><?php echo $libelle ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <h3 data-toggle="collapse" data-target="#collapse-1" aria-expanded="false" aria-controls="collapse-1">Données pour les types de dossier</h3>
    <div class="menu collapse <?php hecho(array_key_exists($menu_gauche_select, $donnees_menu) ? "show" : ""); ?>" id="collapse-1">
        <ul>
            <?php foreach ($donnees_menu as $url => $libelle) : ?>
                <li>
                    <a class="<?php echo $menu_gauche_select == $url ? "actif" : "" ?>" href='<?php $this->url(get_hecho($url . "?id_e=$id_e")); ?>'><?php echo $libelle ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
