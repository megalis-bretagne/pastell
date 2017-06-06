<?php

class ConnecteurFrequenceCest {

    public function displayConnecteurFrequenceInConnecteurEdition(AcceptanceTester $I){
        $I->wantTo("vérifier que la fréquence du connecteur s'affiche sur la page connecteur");
        $I->amLoggedAsAdmin();
        $I->amOnPage("Daemon/config");
        $I->click("Nouveau");
        $I->fillField("Expression","10 X 2");
        $I->fillField("Verrou","VERROU");
        $I->click("Éditer");
        $I->see("Détail sur la fréquence d'un connecteur");
        $I->see("Toutes les 10 minutes (2 fois)");
        $I->see("Verrouiller le travail");
        $I->see("VERROU");

        $I->amOnPage("/Entite/connecteur?id_e=1");
        $I->see("Liste des connecteurs");

        $I->click("Nouveau");
        $I->fillField("Libellé de l'instance","bouchon signature");
        $I->selectOption("Connecteur","fakeIparapheur");
        $I->click("Créer un connecteur");
        $I->see("Connecteur ajouté avec succès");

        $I->click("//tr[contains(td, 'bouchon signature')]//a");
        //$I->click("Configurer");

        $I->see("Toutes les 10 minutes (2 fois)");
        $I->see("Verrouiller le travail");
        $I->see("VERROU");
    }

}