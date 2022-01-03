<?php

class EntityCest
{
    public function creation(AcceptanceTester $I)
    {
        $I->wantTo("créer une nouvelle entité");
        $I->amLoggedAsAdmin();
        $I->amOnPage("/Entite/detail");
        $I->click("Ajouter");
        $I->see("Ajout d'une entité");
        $entity_name = "ZZZ_" . date("YmdHis");
        $I->fillField("Nom", $entity_name);
        $I->fillField("SIREN", "000000000");
        $I->click("#entity-edit");
        $I->see("$entity_name - Informations");
        $I->see("000000000");
        $id_e = $I->grabFromCurrentUrl('#^/Entite/detail\?id_e=(\d+)$#');
        $I->click("#journal_link");
        $I->see("Création de l'entité $entity_name - 000000000");
    }
}
