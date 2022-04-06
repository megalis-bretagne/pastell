<?php

$onglet_tab  = [
    "" => "Gestionnaire de tâches",
    "job" => "Tous les travaux",
    "job?filtre=actif" => "Travaux actifs",
    "job?filtre=lock" => "Travaux suspendus",
    "job?filtre=wait" => "Travaux en attente"
];
?>

<ul class="nav nav-pills">
    <?php foreach ($onglet_tab as $onglet_url => $onglet_name) : ?>
    <li <?php echo ($onglet_url == $page_url) ? 'class="active"' : '' ?>>
        <a href='daemon/<?php echo $onglet_url?>'>
            <?php echo $onglet_name?>
        </a>²
    </li>
    <?php endforeach;?>
</ul>

    