<?php

class RoleCest
{
    public function listeRole(NoGuy $I)
    {
        $I->wantTo("lister les rôles");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/role");
        $I->verifyJsonResponseOK([['role' => 'admin','libelle' => 'Administrateur']]);
    }
}
