#! /usr/bin/php
<?php

/**
 * @var ObjectInstancier $objectInstancier
 */

require_once __DIR__ . '/../init.php';

//ex: php add-action-connecteur.php parametrage-flux-facture-cpp supprimer-factures

$id_connecteur = get_argv(1);
$id_action = get_argv(2);

if (count($argv) != 3) {
    echo "{$argv[0]} : Déclenche une action sur tous les connecteurs d'un certain type\n";
    echo "Usage : {$argv[0]} id_connecteur id_action\n";
    exit;
}

$result = $objectInstancier->getInstance(ConnecteurEntiteSQL::class)->getAllById($id_connecteur);

if (!$result) {
    echo "Il n'y a pas de connecteur de type $id_connecteur\n";
    exit;
}

$list_connecteur = [];

foreach ($result as $connecteur) {
    $id_ce = $connecteur['id_ce'];

    echo "\n";
    echo 'Entite: ' . "{$connecteur['id_e']}" . ', connecteur: ' . "{$connecteur['id_ce']} \n";

    $actionPossible = $objectInstancier->getInstance(ActionPossible::class);
    if (! $actionPossible->isActionPossibleOnConnecteur($id_ce, 1, $id_action)) {
        echo "L'action « $action »  n'est pas permise : " . $actionPossible->getLastBadRule() . "\n";
    } else {
        echo "L'action va être déclenchée \n";
        $list_connecteur[] = $id_ce;
    }

    echo "\n";
}
$nb_connecteur = count($list_connecteur);
echo "L'action $id_action va être déclenchée pour $nb_connecteur connecteurs\n";


echo "Etes-vous sur (o/N) ? ";
$fh = fopen('php://stdin', 'r');
$entree = trim(fgets($fh, 1024));

if ($entree != 'o') {
    exit;
}
foreach ($list_connecteur as $id_ce) {
    echo "\n";
    echo "connecteur $id_ce \n";

    $actionPossible = $objectInstancier->getInstance(ActionPossible::class);
    if (! $actionPossible->isActionPossibleOnConnecteur($id_ce, 1, $id_action)) {
        echo "L'action « $action »  n'est pas permise : " . $actionPossible->getLastBadRule() . "\n";
    } else {
        $objectInstancier->getInstance(ActionExecutorFactory::class)->executeOnConnecteur($id_ce, 1, $id_action);
        echo $objectInstancier->getInstance(ActionExecutorFactory::class)->getLastMessage();
    }

    echo "\n";
}
echo "Les actions ont été déclenchées\n";
