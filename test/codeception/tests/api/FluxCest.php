<?php

class FluxCest
{
    public function listFlux(NoGuy $I)
    {
        $I->wantTo("lister les flux disponibles sur la plateforme");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/flux");
        $I->verifyJsonResponseOK(['actes-generique' => ['nom' => 'Actes (générique) - déprécié']]);
    }

    public function detailFlux(NoGuy $I)
    {
        $I->wantTo("connaitre les propriétés d'un flux");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/flux/actes-generique");
        $I->verifyJsonResponseOK(["acte_nature" => ['name' => "Nature de l'acte"]]);
    }

    public function actionFlux(NoGuy $I)
    {
        $I->wantTo("connaitre les actions sur un flux");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/flux/actes-generique/action");
        $I->verifyJsonResponseOK(['creation' => ['name' => 'Créé']]);
    }
}
