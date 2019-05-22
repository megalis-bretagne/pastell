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
        $I->click("Créer");
        $I->see("Modification d'un document « Actes (générique) » ( Bourg-en-Bresse )");
        $I->canSeeInCurrentUrl("/Document/edition?id_e=1");
        $I->selectOption("Nature de l'acte","1");
        $I->fillField("Numéro de l'acte",date("YmdHis"));
        $I->fillField("Objet","Délibération de test");
        $I->click("Enregistrer");
		$I->click("Cheminement");
		$I->click("Modifier");
        $I->click("Sélectionner dans la classification en matière et sous-matière");
        $I->click("1.1 - Marches publics");
        $I->see("1.1 Marches publics");
        $I->checkOption("Transmission à la signature");
        $I->checkOption("Transmission au contrôle de légalité");
        $I->checkOption("Transmission à la GED");
        $I->checkOption("Transmission au SAE");
        $I->click("Enregistrer");
        $I->see("Sous-type i-Parapheur	");


		$id_d = $I->grabFromCurrentUrl("#id_d=([^&]*)&#");

		/* Horrible hack car codeception et flowjs ca fait deux ... */
		$ob = ObjectInstancierFactory::getObjetInstancier();
		$internalAPI = $ob->getInstance(InternalAPI::class);
		$internalAPI->setUtilisateurId(0);

		$internalAPI->post(
			"/entite/1/document/$id_d/file/arrete",
			array(
				'file_name'=>'actes.pdf',
				'file_content'=>'foo'
			)
		);
		/* Fin du hack */

        $I->click("Sélectionner un sous-type");
        $I->see("Choix d'un type de document");
        $I->selectOption("Sous-type i-Parapheur","Arrêté individuel");
        $I->click("valider");
        $I->see("Actes");
        $I->click("Enregistrer");


        $I->click("Transmettre au parapheur");
        $I->see("Le document a été envoyé au parapheur électronique");
        $I->click("Parapheur");
        $I->click("Vérifier le statut de signature");
        $I->see("Signature récupérée");
        $I->click("Transmettre au TdT");
        $I->see("Le document a été envoyé au contrôle de légalité");
        $I->click("Vérifier le statut de la transaction");
        $I->see("Acquitté par la préfecture");
        $I->click("Verser à la GED");
        $I->see("L'action Versé à la GED a été executée sur le document");
        /*$I->click("Verser au SAE");
        $I->see("Le document a été envoyé au SAE");*/
    }

}