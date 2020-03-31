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

$donnees_menu = array(
    "MailSec/annuaire" => "Annuaire (mail sécurisé)",
    "Entite/agents" => "Agents (Actes)",
);


?>

<div id="main_gauche">

    <h2>Administration</h2>
    <div class="menu">
        <ul>
            <?php foreach ($admninistration_menu as $url => $libelle) : ?>
                <li>
                    <a class="<?php echo $menu_gauche_select == $url ? "actif" : "" ?>" href='<?php $this->url(get_hecho($url . "?id_e=$id_e")); ?>'><?php echo $libelle ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <h2>Données pour les types de dossier</h2>
    <div class="menu">
        <ul>
            <?php foreach ($donnees_menu as $url => $libelle) : ?>
                <li>
                    <a class="<?php echo $menu_gauche_select == $url ? "actif" : "" ?>" href='<?php $this->url(get_hecho($url . "?id_e=$id_e")); ?>'><?php echo $libelle ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

