<?php

class LoginCest {

    public function welcome(AcceptanceTester $I) {
        $I->wantTo("m'assurer que la page de login fonctionne");
        $I->amAnonymous();
        $I->amOnPage("/");
        $I->see('Merci de vous identifier');
    }

    public function connexion(AcceptanceTester $I) {
        $I->wantTo('me connecter au site');
        $I->amAnonymous();
        $I->login("admin","admin");
        $I->see("Liste des documents");
    }

    public function deconnexion(AcceptanceTester $I) {
        $I->wantTo("me déconnecter du site");
        $I->amAnonymous();
        $I->amOnPage("/");
        $I->click("Se déconnecter");
        $I->dontSee("Liste des documents");
        $I->see("Merci de vous identifier");
        $I->seeInCurrentUrl("/Connexion/connexion");
    }

    public function dontSeeOldName(AcceptanceTester $I){
        $I->wantTo("voir qu'il n'y plus de référence à l'ancien nom Libriciel");
        $I->amAnonymous();
        $I->amOnPage("/");
        $I->dontSeeInSource("Adullact");
        $I->dontSee("Sigmalis");
    }

}
