<?php

class RoleCest {

    public function listeRole(NoGuy $I){
        $I->wantTo("lister les rôles");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/role");
        $I->verifyJsonResponseOK(array(array('role'=>'admin','libelle'=>'Administrateur')));
    }

}