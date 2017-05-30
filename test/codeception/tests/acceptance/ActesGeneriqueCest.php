<?php

class ActesGeneriqueCest {

    public function testall(AcceptanceTester $I){
        $I->wantTo("faire une boucle Actes générique complète");
        $I->amLoggedAsAdmin();
        $I->disableDaemon();
        $I->amOnPage("/");
        //$I->click("Bourg-en-Bresse");
        $I->amOnPage("/Document/index?type=&id_e=1");
        $I->click("Actes (générique)");
        $I->see("Liste des documents Actes (générique) pour Bourg-en-Bresse");
        $I->canSeeInCurrentUrl("/Document/list?id_e=1&type=actes-generique");
        $I->click("Nouveau");
        $I->see("Edition d'un document « Actes (générique) » ( Bourg-en-Bresse )");
        $I->canSeeInCurrentUrl("/Document/edition?type=actes-generique&id_e=1");
        $I->selectOption("Nature de l'acte","1");
        $I->fillField("Numéro de l'acte",date("YmdHis"));
        $I->fillField("Objet","Délibération de test");
        $I->attachFile("Acte", "vide.pdf");
        $I->click("Suivant");
        $I->click("liste des matières et sous-matières");
        $I->click("1.1 - Marches publics");
        $I->see("1.1 Marches publics");
        $I->checkOption("Transmission à la signature");
        $I->checkOption("Transmission au contrôle de légalité");
        $I->checkOption("Transmission à la GED");
        $I->checkOption("Transmission au SAE");
        $I->click("Enregistrer");
        $I->see("Sous Type iParapheur");
        $I->click("liste des types");
        $I->see("Choix d'un type de document");
        $I->selectOption("Sous-type i-Parapheur","Arrêté individuel");
        $I->click("Sélectionner");
        $I->see("Actes");
        $I->click("Enregistrer");
        $I->click("Transmettre au parapheur");
        $I->see("Le document a été envoyé au parapheur électronique");
        $I->click("Parapheur");
        $I->click("Vérifier le statut de signature");
        $I->see("Signature récuperée");
        $I->click("Transmettre à la préfecture");
        $I->see("Le document a été envoyé au contrôle de légalité");
        $I->click("Vérifier le statut de la transaction");
        $I->see("Acquitté par la préfecture");
        $I->click("Verser à la GED");
        $I->see("L'action Versé à la GED a été executé sur le document");
        /*$I->click("Verser au SAE");
        $I->see("Le document a été envoyé au SAE");*/
    }

}