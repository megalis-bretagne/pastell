<?php

class DocumentCest
{
    public function listDocument(NoGuy $I)
    {
        $I->wantTo("lister les documents");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $I->sendGET("/entite/1/document");
        $I->verifyJsonResponseOK(array());
    }

    public function listDocumentV1(NoGuy $I)
    {
        $I->wantTo("lister les documents [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $I->sendGETV1("list-document.php?id_e=1");
        $I->verifyJsonResponseOK(array());
    }

    public function rechercheDocumentV1(NoGuy $I)
    {
        $I->wantTo("rechercher les documents [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $I->sendGETV1("recherche-document.php?id_e=1");
        $I->verifyJsonResponseOK(array());
    }

    public function createDocument(NoGuy $I)
    {
        $I->wantTo("créer un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document", array('type' => 'actes-generique'));
        $I->verifyJsonResponseOK(
            array("info" => array("type" => "actes-generique")),
            \Codeception\Util\HttpCode::CREATED
        );
    }

    public function createDocumentV1(NoGuy $I)
    {
        $I->wantTo("créer un document [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOSTV1("create-document.php", array('id_e' => 1,'type' => 'actes-generique'));
        $I->verifyJsonResponseOK(
            array("info" => array("type" => "actes-generique")),
            \Codeception\Util\HttpCode::OK
        );
    }

    public function detailDocument(NoGuy $I)
    {
        $I->wantTo("avoir le détail d'un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGET("/entite/1/document/$id_d");
        $I->verifyJsonResponseOK(array('info' => array('id_d' => $id_d)));
    }

    public function detailDocumentV1(NoGuy $I)
    {
        $I->wantTo("avoir le détail d'un document [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGETV1("detail-document.php?id_e=1&id_d=$id_d");
        $I->verifyJsonResponseOK(
            array(
                'info' => array('id_d' => $id_d),
                'action-possible' => array(
                    'modification','supression'
                ),
                'action_possible' => array(
                    'modification','supression'
                )
            )
        );
    }

    public function detailSeveralDocumentV1(NoGuy $I)
    {
        $I->wantTo("avoir le détail de plusieurs documents [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d_1 = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d_2 = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGETV1("detail-several-document.php?id_e=1&id_d[]=$id_d_1&id_d[]=$id_d_2");
        $I->verifyJsonResponseOK(
            array(
                $id_d_1 => array('info' => array('id_d' => $id_d_1)),
                $id_d_2 => array('info' => array('id_d' => $id_d_2))
            )
        );
    }

    public function modifDocument(NoGuy $I)
    {
        $I->wantTo("modifier un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPATCH("/entite/1/document/$id_d", array('objet' => 'école'));
        $I->verifyJsonResponseOK(array('content' => array('data' => array('objet' => 'école'))));
    }

    public function modifDocumentISO(NoGuy $I)
    {
        $I->wantTo("modifier un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPATCH("/entite/1/document/$id_d", array('objet' => utf8_decode('école')));
        $I->verifyJsonResponseOK(
            array(
                'status' => 'error',
                'error-message' => "Impossible d'encoder le résultat en JSON [code 5]: Malformed UTF-8 characters, possibly incorrectly encoded")
        );
    }

    public function modifDocumentV1(NoGuy $I)
    {
        $I->wantTo("modifier un document [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];

        $objet = utf8_decode("école");

        $I->sendPOSTV1("modif-document.php", array('id_e' => 1,'id_d' => $id_d,'objet' => $objet));
        $I->verifyJsonResponseOK(
            array('content' => array('data' => array('objet' => 'école')))
        );
    }

    public function modifDocumentV1UTF8(NoGuy $I)
    {
        $I->wantTo("modifier un document [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];

        $objet = "école";

        $I->sendPOSTV1("modif-document.php", array('id_e' => 1,'id_d' => $id_d,'objet' => $objet));
        $I->verifyJsonResponseOK(
            array('content' => array('data' => array('objet' => 'Ã©cole')))
        );
    }

    public function envoyerFichier(NoGuy $I)
    {
        $I->wantTo("poster un fichier sur un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=actes-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPOST(
            "/entite/1/document/$id_d/file/arrete",
            array('file_name' => 'toto.pdf','file_content' => file_get_contents(__DIR__ . "/../_data/vide.pdf"))
        );
        $I->verifyJsonResponseOK(
            array(
                'data' => array(
                    'arrete' => array(
                        'toto.pdf'
                    )
                )
            ),
            \Codeception\Util\HttpCode::CREATED
        );
        $I->sendGET("/entite/1/document/$id_d/file/arrete");
        $I->seeResponseEquals(file_get_contents(__DIR__ . "/../_data/vide.pdf"));
    }


    public function envoyerFichierOnChange(NoGuy $I)
    {
        $I->wantTo("poster un fichier sur un document qui déclenche un onchange");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=helios-generique");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPOST(
            "/entite/1/document/$id_d/file/fichier_pes",
            array('file_name' => 'PES_ALR2_TEST.xml','file_content' => file_get_contents(__DIR__ . "/../_data/HELIOS_SIMU_ALR2_1546508114_320550647.xml"))
        );


        $I->verifyJsonResponseOK(
            array(
                "data" => [
            "fichier_pes" => [
                "PES_ALR2_TEST.xml"
            ],
              "objet" => "HELIOS_SIMU_ALR2_1546508114_320550647.xml",
              "id_coll" => "12345678912345",
              "dte_str" => "2019-01-03",
              "cod_bud" => "12",
              "exercice" => "2009",
              "id_bordereau" => "1234567",
              "id_pj" => "",
              "id_pce" => "832",
              "id_nature" => "6553",
              "id_fonction" => "113",
              "etat_ack" => "0"
                ],

            ),
            \Codeception\Util\HttpCode::CREATED
        );
        $I->sendGET("/entite/1/document/$id_d/file/fichier_pes");
        $I->seeResponseEquals(file_get_contents(__DIR__ . "/../_data/HELIOS_SIMU_ALR2_1546508114_320550647.xml"));
    }


    public function getExternalData(NoGuy $I)
    {
        $I->wantTo("récupérer une liste de données externe");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=test");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGET("/entite/1/document/$id_d/externalData/test_external_data");
        $I->verifyJsonResponseOK(array("Spock"));
    }

    public function patchExternalData(NoGuy $I)
    {
        $I->wantTo("Envoyer une données externe");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=test");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPATCH("/entite/1/document/$id_d/externalData/test_external_data", array('choix' => 'Spock'));
        $I->verifyJsonResponseOK(array('result' => 'ok','data' => ['test_external_data' => 'Spock']));
    }


    public function getExternalDataV1(NoGuy $I)
    {
        $I->wantTo("récupérer une liste de données externe [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=test");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGETV1("external-data.php?id_e=1&id_d=$id_d&field=test_external_data");
        $I->verifyJsonResponseOK(array("Spock"));
    }

    public function action(NoGuy $I)
    {
        $I->wantTo("faire une action sur un document");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=test");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendPOST("/entite/1/document/$id_d/action/ok");
        $I->verifyJsonResponseOK(
            array("result" => true,"message" => "OK !"),
            \Codeception\Util\HttpCode::CREATED
        );
    }

    public function actionV1(NoGuy $I)
    {
        $I->wantTo("faire une action sur un document [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/document?type=test");
        $id_d = $I->grabDataFromResponseByJsonPath('$.id_d')[0];
        $I->sendGETV1("action.php?id_e=1&id_d=$id_d&action=ok");
        $I->verifyJsonResponseOK(
            array("result" => "1","message" => "OK !"),
            \Codeception\Util\HttpCode::OK
        );
    }
}
