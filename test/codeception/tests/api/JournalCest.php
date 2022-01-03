<?php

class JournalCest
{
    public function journal(NoGuy $I)
    {
        $I->wantTo("récupérer la liste des événements du journal");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/journal");
        $I->verifyJsonResponseOK(array());
    }

    public function journalV1(NoGuy $I)
    {
        $I->wantTo("récupérer la liste des événements du journal [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("journal.php");
        $I->verifyJsonResponseOK(array());
    }

    public function detailEvenement(NoGuy $I)
    {
        $I->wantTo("récupérer le détail d'un événement du journal");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite", array('denomination' => 'foo','type' => 'collectivite','siren' => '000000000'));
        $I->sendGET("/journal");
        $id_j = $I->grabDataFromResponseByJsonPath('$..id_j')[0];
        $I->sendGET("/journal/$id_j");
        $I->verifyJsonResponseOK(array('id_j' => $id_j));
    }

    public function jeton(NoGuy $I)
    {
        $I->wantTo("récupérer le jeton d'un événement du journal");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite", array('denomination' => 'foo','type' => 'collectivite','siren' => '000000000'));
        $I->sendGET("/journal");
        $id_j = $I->grabDataFromResponseByJsonPath('$..id_j')[0];
        $I->sendGET("/journal/$id_j/jeton");
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }
}
