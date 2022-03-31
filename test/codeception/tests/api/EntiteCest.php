<?php

class EntiteCest
{
    public function tryListEntite(NoGuy $I)
    {
        $I->wantTo("lister les entités auxquelles j'ai accès");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/entite");
        $this->verifyListEntite($I);
    }

    public function tryListEntiteV1(NoGuy $I)
    {
        $I->wantTo("lister les entités auxquelles j'ai accès [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("/list-entite.php");
        $this->verifyListEntite($I);
    }

    private function verifyListEntite(NoGuy $I)
    {
        $I->verifyJsonResponseOK(
            [
                [
                    "id_e" => 1,
                    "denomination" => "Bourg-en-Bresse",
                    "siren" => "000000000",
                    "type" => "collectivite",
                    "centre_de_gestion" => "0",
                    "entite_mere" => "0"
                ]
            ]
        );
    }

    public function detailEntite(NoGuy $I)
    {
        $I->wantTo("voir le détail d'une entité");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/entite/1");
        $this->verifyDetailEntite($I);
    }

    public function detailEntiteV1(NoGuy $I)
    {
        $I->wantTo("voir le détail d'une entité [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("/detail-entite.php?id_e=1");
        $this->verifyDetailEntite($I);
    }

    private function verifyDetailEntite(NoGuy $I)
    {
        $I->verifyJsonResponseOK(
            [
                "id_e" => 1,
                "denomination" => "Bourg-en-Bresse",
                "siren" => "000000000",
                "type" => "collectivite",
                "centre_de_gestion" => "0",
                "entite_mere" => "0",
                "entite_fille" => []
            ]
        );
    }

    public function testCreateEntite(NoGuy $I)
    {
        $I->wantTo("créer une entité");
        $I->amHttpAuthenticatedAsAdmin();
        $input = ['denomination' => 'Brindur','siren' => '000000000','type' => 'collectivite'];
        $I->sendPOST("/entite", $input);
        $I->verifyJsonResponseOK($input, \Codeception\Util\HttpCode::CREATED);
    }

    public function testCreateEntiteV1(NoGuy $I)
    {
        $I->wantTo("créer une entité [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $input = ['denomination' => 'Wencifa','siren' => '000000000','type' => 'collectivite'];
        $I->sendPOSTV1("/create-entite.php", $input);
        $I->verifyJsonResponseOK($input, \Codeception\Util\HttpCode::OK);
    }

    private function createEntite(NoGuy $I, string $denomination)
    {
        $input = [
            'denomination' => $denomination,
            'siren' => '000000000',
            'type' => 'collectivite'
        ];
        $I->sendPOST("/entite", $input);
        return $I->grabDataFromResponseByJsonPath("$.id_e")[0];
    }

    public function modifEntite(NoGuy $I)
    {
        $I->wantTo("modifier une entité");
        $I->amHttpAuthenticatedAsAdmin();
        $id_e = $this->createEntite($I, 'Corder');
        $I->sendPATCH("/entite/{$id_e}", ['denomination' => 'Darden']);
        $I->verifyJsonResponseOK([
                'denomination' => 'Darden'
            ]);
    }

    public function modifEntiteV1(NoGuy $I)
    {
        $I->wantTo("modifier une entité [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $id_e = $this->createEntite($I, 'Corder');
        $I->sendPOSTV1("modif-entite.php", ['id_e' => $id_e,'denomination' => 'Darden']);
        $I->verifyJsonResponseOK([
                'denomination' => 'Darden'
            ]);
    }

    public function deleteEntite(NoGuy $I)
    {
        $I->wantTo("supprimer une entité");
        $I->amHttpAuthenticatedAsAdmin();
        $id_e = $this->createEntite($I, 'Corder');
        $I->sendDELETE("/entite/$id_e");
        $I->verifyJsonResponseOK(["result" => "ok"]);
        $I->sendGET("/entite/$id_e");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
    }

    public function deleteEntiteV1(NoGuy $I)
    {
        $I->wantTo("supprimer une entité [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $id_e = $this->createEntite($I, 'Corder');
        $I->sendGETV1("delete-entite.php?id_e=$id_e");
        $I->verifyJsonResponseOK(["result" => "ok"]);
        $I->sendGET("/entite/$id_e");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
    }
}
