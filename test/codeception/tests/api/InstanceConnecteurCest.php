<?php

class InstanceConnecteurCest {

    public function listInstanceConnecteur(NoGuy $I){
        $I->wantTo("lister toutes les instances d'un connecteur");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/connecteur/all");
        $I->verifyJsonResponseOK(array());
    }

    public function listInstanceSpecificConnecteur(NoGuy $I){
        $I->wantTo("lister toutes les instances d'un même connecteur");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/connecteur/all/fakeIparapheur");
        $I->verifyJsonResponseOK(array(array('libelle'=>'Bouchon de signature')));
    }

    public function listInstanceEntiteConnecteur(NoGuy $I){
        $I->wantTo("lister toutes les instances d'une entité");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/entite/1/connecteur/");
        $I->verifyJsonResponseOK(array(array('libelle'=>'Bouchon de signature')));
    }

    public function listInstanceEntiteConnecteurV1(NoGuy $I){
        $I->wantTo("lister toutes les instances d'une entité [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("list-connecteur-entite.php?id_e=1");
        $I->verifyJsonResponseOK(array(array('libelle'=>'Bouchon de signature')));
    }

    public function detailConnecteur(NoGuy $I){
        $I->wantTo("avoir le détail d'un connecteur");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/entite/1/connecteur/2");
        $I->verifyJsonResponseOK(array('libelle'=>'Bouchon de signature'));
    }

    public function detailConnecteurV1(NoGuy $I){
        $I->wantTo("avoir le détail d'un connecteur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("detail-connecteur-entite.php?id_e=1&id_ce=2");
        $I->verifyJsonResponseOK(array('libelle'=>'Bouchon de signature'));
    }

    public function creationConnecteur(NoGuy $I){
        $I->wantTo("créer un connecteur");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/connecteur?id_connecteur=s2low&libelle=test_s2low");
        $I->verifyJsonResponseOK(
            array('libelle'=>'test_s2low'),
            \Codeception\Util\HttpCode::CREATED
        );
    }

    public function creationConnecteurV1(NoGuy $I){
        $I->wantTo("créer un connecteur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOSTV1(
            "create-connecteur-entite.php",
            array(
                'id_e'=>1,
                'id_connecteur'=>'s2low',
                'libelle'=>'test_s2low'
            )
        );
        $I->verifyJsonResponseOK(
            array('libelle'=>'test_s2low'),
            \Codeception\Util\HttpCode::CREATED
        );
    }

    private function createConnecteur(NoGuy $I){
        $I->sendPOST("/entite/1/connecteur?id_connecteur=s2low&libelle=test_s2low");
        $id_ce = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        return $id_ce;
    }

    public function modifierLibelle(NoGuy $I){
        $I->wantTo("modifier le libellé d'un connecteur");
        $I->amHttpAuthenticatedAsAdmin();
        $id_ce = $this->createConnecteur($I);
        $I->sendPATCH("/entite/1/connecteur/$id_ce",array('libelle'=>'foo'));
        $I->verifyJsonResponseOK(
            array('libelle'=>'foo')
        );
    }

    public function modifierLibelleV1(NoGuy $I){
        $I->wantTo("modifier le libellé d'un connecteur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $id_ce = $this->createConnecteur($I);
        $I->sendPOSTV1("edit-connecteur-entite.php",array('id_e'=>1,'id_ce'=>$id_ce,'libelle'=>'foo'));
        $I->verifyJsonResponseOK(
            array('libelle'=>'foo')
        );
    }

    public function modifierPropriete(NoGuy $I){
        $I->wantTo("modifier une propriété d'un connecteur");
        $I->amHttpAuthenticatedAsAdmin();
        $id_ce = $this->createConnecteur($I);
        $I->sendPATCH("/entite/1/connecteur/$id_ce/content",array('url'=>'https://s2low.org'));
        $I->verifyJsonResponseOK(array('data'=>array('url'=>'https://s2low.org')));
    }

    public function voirExternalData(NoGuy $I){
        $I->wantTo("lister les possibilités d'un external data");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/connecteur?id_connecteur=recuperation-fichier-local&libelle=recup1");
        $id_ce_recup = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendPOST("/entite/1/connecteur?id_connecteur=creation-document&libelle=create1");
        $id_ce_creation = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendGET("/entite/1/connecteur/$id_ce_creation/externalData/connecteur_recup");
        $I->verifyJsonResponseOK(array($id_ce_recup=>'recup1'));
    }

    public function selectExternalData(NoGuy $I){
        $I->wantTo("selectionner une propriété external data");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/connecteur?id_connecteur=recuperation-fichier-local&libelle=recup1");
        $id_ce_recup = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendPOST("/entite/1/connecteur?id_connecteur=creation-document&libelle=create1");
        $id_ce_creation = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendPATCH("/entite/1/connecteur/$id_ce_creation/externalData/connecteur_recup",
            array('connecteur_recup'=>$id_ce_recup)
        );
        $I->verifyJsonResponseOK(array('data'=>array('connecteur_recup'=>'recup1')));
    }

    public function envoyerFichier(NoGuy $I){
        $I->wantTo("poster un fichier sur un connecteur");
        $I->amHttpAuthenticatedAsAdmin();
        $id_ce = $this->createConnecteur($I);
        $I->sendPOST("/entite/1/connecteur/$id_ce/file/server_certificate",
            array('file_name'=>'toto.txt','file_content'=>'test1')
        );
        $I->verifyJsonResponseOK(
            array(
                'data' => array(
                    'server_certificate' => array(
                        'toto.txt'
                    )
                )
            ),
            \Codeception\Util\HttpCode::CREATED
        );
        $I->sendGET("/entite/1/connecteur/$id_ce/file/server_certificate");
        $I->seeResponseEquals("test1");
    }

    public function actionConnecteur(NoGuy $I){
        $I->wantTo("déclencher une action sur un connecteur");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/connecteur?id_connecteur=test&libelle=test");
        $id_ce = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendPOST("/entite/1/connecteur/$id_ce/action/ok");
        $I->verifyJsonResponseOK(
            array("result"=>true,'last_message'=>'OK !'),
            \Codeception\Util\HttpCode::CREATED
        );
    }

    public function supprimerConnecteur(NoGuy $I){
        $I->wantTo("supprimer un connecteur");
        $I->amHttpAuthenticatedAsAdmin();
        $id_ce = $this->createConnecteur($I);
        $I->sendDELETE("/entite/1/connecteur/$id_ce");
        $I->verifyJsonResponseOK(array('result'=>'ok'));
        $I->sendGET("/entite/1/connecteur/$id_ce");
        $I->verifyJsonResponseOK(
            array('error-message'=>'Ce connecteur n\'existe pas.'),
            \Codeception\Util\HttpCode::BAD_REQUEST
        );
    }

    public function supprimerConnecteurV1(NoGuy $I){
        $I->wantTo("supprimer un connecteur [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $id_ce = $this->createConnecteur($I);
        $I->sendGETV1("delete-connecteur-entite.php?id_e=1&id_ce=$id_ce");
        $I->verifyJsonResponseOK(array('result'=>'ok'));
        $I->sendGET("/entite/1/connecteur/$id_ce");
        $I->verifyJsonResponseOK(
            array('error-message'=>'Ce connecteur n\'existe pas.'),
            \Codeception\Util\HttpCode::BAD_REQUEST
        );
    }
}