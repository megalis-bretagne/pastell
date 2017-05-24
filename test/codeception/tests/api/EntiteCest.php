<?php

class EntiteCest {

    public function tryListEntite(NoGuy $I){
        $I->wantTo("lister les entités auxquelles j'ai accès");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/entite");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            array(
                array(
                    "id_e"=>1,
                    "denomination" => "Bourg-en-Bresse",
                    "siren" => "000000000",
                    "type" => "collectivite",
                    "centre_de_gestion" => "0",
                    "entite_mere" => "0"
                )
            )
        );
    }

    public function tryListEntiteV1(NoGuy $I){
        $I->wantTo("lister les entités auxquelles j'ai accès [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("/list-entite.php");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            array(
                array(
                    "id_e"=>1,
                    "denomination" => "Bourg-en-Bresse",
                    "siren" => "000000000",
                    "type" => "collectivite",
                    "centre_de_gestion" => "0",
                    "entite_mere" => "0"
                )
            )
        );
    }




}
