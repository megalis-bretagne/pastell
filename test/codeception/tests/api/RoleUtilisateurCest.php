<?php

class RoleUtilisateurCest
{
    public function listRoleUtilisateur(NoGuy $I)
    {
        $I->wantTo("lister les rôles d'un utilisateur");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/utilisateur/1/role");
        $I->verifyJsonResponseOK([['id_u' => 1,'id_e' => 0,'role' => 'admin']]);
    }

    public function listRoleUtilisateurV1(NoGuy $I)
    {
        $I->wantTo("lister les rôles d'un utilisateur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("list-role-utilisateur.php?id_u=1");
        $I->verifyJsonResponseOK([['id_u' => 1,'id_e' => 0,'role' => 'admin']]);
    }

    private function createUser(NoGuy $I, $login)
    {
        $user_info = [
            'nom' => 'foo',
            'login' => $login,
            'prenom' => 'baz',
            'email' => 'toto@toto.fr',
        ];
        $user_info['password'] = '*D71m@!lzHZCCfJw7Qc&G8b3b';
        $I->sendPOST(
            "/utilisateur",
            $user_info
        );
        return $I->grabDataFromResponseByJsonPath("$.id_u")[0];
    }

    private function createUserWithRole(NoGuy $I, $login)
    {
        $id_u = $this->createUser($I, "$login");
        $I->sendPOST("/utilisateur/$id_u/role", ['role' => 'admin']);
        return $id_u;
    }

    public function ajouterRoleUtilisateur(NoGuy $I)
    {
        $I->wantTo("ajouter un rôle à un utilisateur");
        $I->amHttpAuthenticatedAsAdmin();
        $id_u = $this->createUser($I, "ajouter-role");
        $I->sendPOST("/utilisateur/$id_u/role", ['role' => 'admin']);
        $I->verifyJsonResponseOK(
            ['result' => 'ok'],
            \Codeception\Util\HttpCode::CREATED
        );
        $I->sendGET("/utilisateur/$id_u/role");
        $I->verifyJsonResponseOK([['id_u' => $id_u,'id_e' => 0,'role' => 'admin']]);
    }

    public function ajouterRoleUtilisateurV1(NoGuy $I)
    {
        $I->wantTo("ajouter un rôle à un utilisateur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $id_u = $this->createUser($I, "ajouter-role-v1");
        $I->sendPOSTV1("add-role-utilisateur.php", ['role' => 'admin','id_u' => $id_u]);
        $I->verifyJsonResponseOK(
            ['result' => 'ok'],
            \Codeception\Util\HttpCode::OK
        );
        $I->sendGET("/utilisateur/$id_u/role");
        $I->verifyJsonResponseOK([['id_u' => $id_u,'id_e' => 0,'role' => 'admin']]);
    }

    public function deleteRoleUtilisateur(NoGuy $I)
    {
        $I->wantTo("enlever un role à un utilisateur");
        $I->amHttpAuthenticatedAsAdmin();
        $id_u = $this->createUserWithRole($I, 'delete-role');
        $I->sendDELETE("/utilisateur/$id_u/role?role=admin");
        $I->verifyJsonResponseOK(
            ['result' => 'ok']
        );
        $I->sendGET("/utilisateur/$id_u/role");
        $I->verifyJsonResponseOK([['id_u' => $id_u,'id_e' => 0,'role' => 'aucun droit']]);
    }

    public function deleteRoleUtilisateurV1(NoGuy $I)
    {
        $I->wantTo("enlever un role à un utilisateur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $id_u = $this->createUserWithRole($I, 'delete-role-v1');
        $I->sendGETV1("delete-role-utilisateur.php?id_u=$id_u&role=admin");
        $I->verifyJsonResponseOK(
            ['result' => 'ok']
        );
        $I->sendGET("/utilisateur/$id_u/role");
        $I->verifyJsonResponseOK([['id_u' => $id_u,'id_e' => 0,'role' => 'aucun droit']]);
    }
}
