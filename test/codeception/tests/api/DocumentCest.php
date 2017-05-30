<?php

class DocumentCest {

    public function listDocument(NoGuy $I){
        $I->wantTo("lister les documents");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $I->sendGET("/entite/1/document");
        $I->verifyJsonResponseOK(array());
    }

    public function listDocumentV1(NoGuy $I){
        $I->wantTo("lister les documents [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $I->sendGETV1("list-document.php?id_e=1");
        $I->verifyJsonResponseOK(array());
    }

    public function rechercheDocumentV1(NoGuy $I){
        $I->wantTo("rechercher les documents [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $I->sendGETV1("recherche-document.php?id_e=1");
        $I->verifyJsonResponseOK(array());
    }

    public function createDocument(NoGuy $I){
        $I->wantTo("créer un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document",array('type'=>'actes-generique'));
        $I->verifyJsonResponseOK(
            array("info"=>array("type"=>"actes-generique")),
            \Codeception\Util\HttpCode::CREATED
        );
    }

    public function createDocumentV1(NoGuy $I){
        $I->wantTo("créer un document [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOSTV1("create-document.php",array('id_e'=>1,'type'=>'actes-generique'));
        $I->verifyJsonResponseOK(
            array("info"=>array("type"=>"actes-generique")),
            \Codeception\Util\HttpCode::CREATED
        );
    }

    public function detailDocument(NoGuy $I){
        $I->wantTo("avoir le détail d'un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGET("/entite/1/document/$id_d");
        $I->verifyJsonResponseOK(array('info'=>array('id_d'=>$id_d)));
    }

    public function detailDocumentV1(NoGuy $I){
        $I->wantTo("avoir le détail d'un document [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGETV1("detail-document.php?id_e=1&id_d=$id_d");
        $I->verifyJsonResponseOK(array('info'=>array('id_d'=>$id_d)));
    }

    public function detailSeveralDocumentV1(NoGuy $I){
        $I->wantTo("avoir le détail de plusieurs documents [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d_1 = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d_2 = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGETV1("detail-several-document.php?id_e=1&id_d[]=$id_d_1&id_d[]=$id_d_2");
        $I->verifyJsonResponseOK(
            array(
                $id_d_1=>array('info'=>array('id_d'=>$id_d_1)),
                $id_d_2=>array('info'=>array('id_d'=>$id_d_2))
            )
        );
    }

    public function modifDocument(NoGuy $I){
        $I->wantTo("modifier un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPATCH("/entite/1/document/$id_d",array('objet'=>'titre42'));
        $I->verifyJsonResponseOK(array('content'=>array('data'=>array('objet'=>'titre42'))));
    }

    public function modifDocumentV1(NoGuy $I){
        $I->wantTo("modifier un document [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPOSTV1("modif-document.php",array('id_e'=>1,'id_d'=>$id_d,'objet'=>'titre42'));
        $I->verifyJsonResponseOK(
            array('content'=>array('data'=>array('objet'=>'titre42')))
        );
    }

    public function envoyerFichier(NoGuy $I){
        $I->wantTo("poster un fichier sur un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPOST("/entite/1/document/$id_d/file/arrete",
            array('file_name'=>'toto.txt','file_content'=>'test1')
        );
        $I->verifyJsonResponseOK(
            array(
                'data' => array(
                    'arrete' => array(
                        'toto.txt'
                    )
                )
            ),
            \Codeception\Util\HttpCode::CREATED
        );
        $I->sendGET("/entite/1/document/$id_d/file/arrete");
        $I->seeResponseEquals("test1");
    }

    public function getExternalData(NoGuy $I){
        $I->wantTo("récupérer une liste de données externe");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=test");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGET("/entite/1/document/$id_d/externalData/test_external_data");
        $I->verifyJsonResponseOK(array("Spock"));
    }

    public function getExternalDataV1(NoGuy $I){
        $I->wantTo("récupérer une liste de données externe [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=test");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGETV1("external-data.php?id_e=1&id_d=$id_d&field=test_external_data");
        $I->verifyJsonResponseOK(array("Spock"));
    }

    public function action(NoGuy $I){
        $I->wantTo("faire une action sur un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=test");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPOST("/entite/1/document/$id_d/action/ok");
        $I->verifyJsonResponseOK(
            array("result"=>true,"message"=>"OK !"),
            \Codeception\Util\HttpCode::CREATED
        );
    }

    public function actionV1(NoGuy $I){
        $I->wantTo("faire une action sur un document [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=test");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGETV1("action.php?id_e=1&id_d=$id_d&action=ok");
        $I->verifyJsonResponseOK(
            array("result"=>true,"message"=>"OK !"),
            \Codeception\Util\HttpCode::CREATED
        );
    }
}