<?php

class LDAPCreateUser extends ActionExecutor
{
    public function go()
    {
        $ldap = $this->getMyConnecteur();
        $utilisateur = $this->objectInstancier->getInstance(UtilisateurSQL::class);
        $users = $ldap->getUserToCreate($utilisateur);

        foreach ($users as $user) {
            if ($user['create']) {
                $password = mt_rand();
                $user['id_u'] = $utilisateur->create($user['login'], $password, $user['email'], "");
                $utilisateur->validMailAuto($user['id_u']);
                $utilisateur->setColBase($user['id_u'], 0);
                $this->objectInstancier->getInstance(RoleUtilisateur::class)->addRole(
                    $user['id_u'],
                    RoleUtilisateur::AUCUN_DROIT,
                    0
                );
                $this->objectInstancier->getInstance(Journal::class)->add(
                    Journal::MODIFICATION_UTILISATEUR,
                    0,
                    0,
                    "Ajout",
                    "Ajout de l'utilisateur {$user['login']} via LDAP"
                );
            }
            if ($user['synchronize']) {
                $utilisateur->setNomPrenom($user['id_u'], $user['nom'], $user['prenom']);
                $utilisateur->setEmail($user['id_u'], $user['email']);
                $this->objectInstancier->getInstance(Journal::class)->add(
                    Journal::MODIFICATION_UTILISATEUR,
                    0,
                    0,
                    "Synchronisation",
                    "Synchronisation de l'utilisateur {$user['login']} via LDAP"
                );
            }
        }
        $this->setLastMessage("Utilisateurs synchronisés");
        return true;
    }
}
