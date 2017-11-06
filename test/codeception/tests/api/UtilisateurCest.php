<?php

class UtilisateurCest {

    private function getUser1(){
        return array('id_u'=>1,'login'=>'admin','email'=>'noreply@libriciel.coop');
    }

    private function getCreatedUser($login = 'bar'){
        return array(
            'nom'=>'foo',
            'login'=>$login,
            'prenom'=>'baz',
            'email'=>'toto@toto.fr',
        );
    }

    public function listUtilisateur(NoGuy $I){
        $I->wantTo("lister les utilisateurs");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/utilisateur");
        $I->verifyJsonResponseOK(
            array(
                $this->getUser1()
            )
        );
    }

    public function listUtilisateurV1(NoGuy $I){
        $I->wantTo("lister les utilisateurs [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("list-utilisateur.php");
        $I->verifyJsonResponseOK(
            array(
                $this->getUser1()
            )
        );
    }

    public function detailUtilisateur(NoGuy $I){
        $I->wantTo("obtenir le détail d'une entité");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/utilisateur/1");
        $I->verifyJsonResponseOK($this->getUser1());
    }

    public function detailUtilisateurV1(NoGuy $I){
        $I->wantTo("obtenir le détail d'une entité [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("detail-utilisateur.php?id_u=1");
        $I->verifyJsonResponseOK($this->getUser1());
    }

    public function creationUtilisateur(NoGuy $I){
        $I->wantTo("créer un utilisateur");
        $I->amHttpAuthenticatedAsAdmin();
        $user_info = $this->getCreatedUser();
        $user_info['password'] = 'password';
        $I->sendPOST(
            "/utilisateur",
            $user_info
        );
        $I->verifyJsonResponseOK(
            $this->getCreatedUser(),
            \Codeception\Util\HttpCode::CREATED
        );
    }

    public function creationUtilisateurV1(NoGuy $I){
        $I->wantTo("créer un utilisateur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $user_info = $this->getCreatedUser('barv1');
        $user_info['password'] = 'password';
        $I->sendPOSTV1(
            "create-utilisateur.php",
            $user_info
        );
        $I->verifyJsonResponseOK(
            $this->getCreatedUser('barv1'),
            \Codeception\Util\HttpCode::OK
        );
    }

    private function createUser(NoGuy $I,$login = 'bar'){
        $user_info = $this->getCreatedUser($login);
        $user_info['password'] = 'password';
        $I->sendPOST(
            "/utilisateur",
            $user_info
        );
        $I->verifyJsonResponseOK(
            $this->getCreatedUser($login),
            \Codeception\Util\HttpCode::CREATED
        );
        return $I->grabDataFromResponseByJsonPath("$.id_u")[0];
    }

    public function modificationUtilisateur(NoGuy $I){
        $I->wantTo("modifier un utilisateur");
        $I->amHttpAuthenticatedAsAdmin();
        $id_u = $this->createUser($I,'modif-user');
        $I->sendPATCH("/utilisateur/$id_u",array('nom'=>'Gaudreau'));
        $info = $this->getCreatedUser('modif-user');
        $info['nom'] = 'Gaudreau';
        $I->verifyJsonResponseOK($info);
    }

    public function modificationUtilisateurV1(NoGuy $I){
        $I->wantTo("modifier un utilisateur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $id_u = $this->createUser($I,'modif-user-v1');
        $I->sendPOSTV1("modif-utilisateur.php",array('id_u'=>$id_u,'nom'=>'Gaudreau'));
        $info = $this->getCreatedUser('modif-user-v1');
        $info['nom'] = 'Gaudreau';
        $I->verifyJsonResponseOK($info);
    }

    public function deleteUtilisateur(NoGuy $I){
        $I->wantTo("supprimer un utilisateur");
        $I->amHttpAuthenticatedAsAdmin();
        $id_u = $this->createUser($I,'delete-user');
        $I->sendDELETE("/utilisateur/$id_u");
        $I->verifyJsonResponseOK(array('result'=>'ok'));
        $I->sendGET("/utilisateur/$id_u");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
    }

    public function deleteUtilisateurV1(NoGuy $I){
        $I->wantTo("supprimer un utilisateur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $id_u = $this->createUser($I,'delete-user-v1');
        $I->sendGETV1("delete-utilisateur.php?id_u=$id_u");
        $I->verifyJsonResponseOK(array('result'=>'ok'));
        $I->sendGET("/utilisateur/$id_u");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
    }
}