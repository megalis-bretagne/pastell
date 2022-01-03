<?php

class SystemFluxCest
{
    public function listerLesFlux(AcceptanceTester $I)
    {
        $I->wantTo("voir que tous les flux disponibles sont valides");
        $I->amLoggedAsAdmin();
        $I->amOnPage("/System/flux");
        $I->see("Types de dossier disponibles");
        $I->dontSee("Erreur sur le flux !");
    }

    public function listerLesConnecteurs(AcceptanceTester $I)
    {
        $I->wantTo("lister les connecteurs");
        $I->amLoggedAsAdmin();
        $I->amOnPage("/System/connecteur");
        $I->see("Connecteurs disponibles");
    }
}
