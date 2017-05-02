<?php


class LoginCest
{
    const PHPSESSID = "PHPSESSID";

    private $session_cookie;

    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function welcome(AcceptanceTester $I) {
        $I->wantTo("m'assurer que la page de login fonctionne");
        $I->amOnPage("/");
        $I->see('Merci de vous identifier');
    }

    public function connexion(AcceptanceTester $I) {
        $I->wantTo('me connecter au site');
        $I->login("admin","admin");
        $this->session_cookie = $I->grabCookie(self::PHPSESSID);
    }

    public function deconnexion(AcceptanceTester $I) {
        $I->wantTo("me déconnecter du site");
        $I->setCookie(self::PHPSESSID,$this->session_cookie);
        //$I->login("admin","admin");
        $I->amOnPage("/");
        $I->click("Se déconnecter");
        $I->dontSee("Liste des documents");
        $I->see("Merci de vous identifier");
        $I->seeInCurrentUrl("/Connexion/connexion");
    }

}
