<?php


class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function welcome(AcceptanceTester $I){
        $I->wantTo("m'assurer que la page de login fonctionne");
        $I->amOnPage("/");
        $I->see('Merci de vous identifier');
    }

    public function connexion(AcceptanceTester $I) {
        $I->wantTo('me connecter au site');
        $I->amOnPage("/");
        $I->fillField('Identifiant','admin');
        $I->fillField('Mot de passe','admin');
        $I->click('Connexion');
        $I->see('Liste des documents');
    }

    public function deconnexion(AcceptanceTester $I){
        $I->wantTo("me déconnecter du site");
        $I->login("admin","admin");
        $I->amOnPage("/");
        $I->click("Se déconnecter");
        $I->dontSee("Liste des documents");
        $I->see("Merci de vous identifier");
    }

}
