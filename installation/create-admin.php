<?php

//TODO a mettre dans une Commande

//Crée un admin (crée aussi le rôle admin et fixe les droits si il n'existe pas

require_once(__DIR__ . "/../init.php");


$login = get_argv(1);
$password = get_argv(2);
$email = get_argv(3);

/** @var AdminControler $adminControler */
$adminControler = $objectInstancier->getInstance(AdminControler::class);

$result = $adminControler->createAdmin($login, $password, $email);

if ($result) {
        echo "Administrateur $login créé avec succès\n";
} else {
    echo $adminControler->getLastError()->getLastError() . "\n";
    echo "Usage : {$argv[0]} login password email\n";
    exit;
}

$adminControler->fixDroit();
