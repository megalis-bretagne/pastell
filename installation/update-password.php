<?php

/**
 * @var ObjectInstancier $objectInstancier
 */

require_once __DIR__ . '/../init.php';

if ($argc < 3) {
    echo "Usage: {$argv[0]} login password\n";
    echo "Change le mot de passe d'un utilisatuer\n";
    exit(-2);
}

$login = get_argv(1);
$password = get_argv(2);

$utilisateur = $objectInstancier->getInstance(UtilisateurSQL::class);

$id_u = $utilisateur->getIdFromLogin($login);
if (! $id_u) {
    echo "Le login $login ne correspond à aucun utilisateur";
    exit(-1);
}
$utilisateur->setPassword($id_u, $password);

echo "Mot de passe modifié\n";
exit(0);
