<?php

class LoginCest {

    public function welcome(AcceptanceTester $I) {
        $I->wantTo("m'assurer que la page de login fonctionne");
        $I->amAnonymous();
        $I->amOnPage("/");
        $I->see('Veuillez saisir vos identifiants de connexion');
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
        $I->see("Veuillez saisir vos identifiants de connexion");
        $I->seeInCurrentUrl("/Connexion/connexion");
    }

    public function dontSeeOldName(AcceptanceTester $I){
        $I->wantTo("voir qu'il n'y plus de référence à l'ancien nom Libriciel");
        $I->amAnonymous();
        $I->amOnPage("/");
        $I->dontSeeInSource("Adullact");
        $I->dontSee("Sigmalis");
    }

    public function redirectToInternPage(AcceptanceTester $I){
        $I->wantTo("être redirigé vers la page que je demandais après une authentification réussie");
        $I->amAnonymous();
        $I->amOnPage("/System/index");
        $I->see("Veuillez saisir vos identifiants de connexion pour accéder à cette page.");
        $I->fillField('Identifiant *','admin');
        $I->fillField('Mot de passe *','admin');
        $I->click('Se connecter');
        $I->see("Test de l'environnement");
    }

}
