<?php

class VersionCest
{
    public function tryVersion(NoGuy $I)
    {
        $I->wantTo('récupérer la version de Pastell');
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGet('version');
        $this->testVersionResponse($I);
    }

    public function tryVersionV1(NoGuy $I)
    {
        $I->wantTo('récupérer la version de Pastell [V1]');
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1('version.php');
        $this->testVersionResponse($I);
    }

    public function tryNotAuthenticated(NoGuy $I)
    {
        $I->wantTo("vérifier qu'on peut pas utiliser l'API sans être authentifié");
        $I->sendGET("/version");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContainsJson(['status' => 'error','error-message' => 'Accès interdit']);
    }

    public function tryNotAuthenticatedV1(NoGuy $I)
    {
        $I->wantTo("vérifier qu'on peut pas utiliser l'API sans être authentifié [V1]");
        $I->sendGETV1("/version.php");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
        $I->seeResponseContainsJson(['status' => 'error','error-message' => 'Accès interdit']);
    }

    public function tryNotAuhtorizedMethod(NoGuy $I)
    {
        $I->wantTo("vérifier qu'on peut pas utiliser POST sur l'objet version");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/version");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::METHOD_NOT_ALLOWED);
    }

    public function tryMethodAuthorizedOnV1(NoGuy $I)
    {
        $I->wantTo("vérifier qu'on peut utiliser POST sur la page version.php [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOSTV1("/version.php");
        $this->testVersionResponse($I);
    }

    private function testVersionResponse(NoGuy $I)
    {
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesXpath("//version");
        $I->seeResponseJsonMatchesXpath("//version_complete");
    }
}
