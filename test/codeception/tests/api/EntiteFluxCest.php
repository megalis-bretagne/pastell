<?php

class EntiteFluxCest
{
    public function listeInstance(NoGuy $I)
    {
        $I->wantTo("lister les instances de connecteurs associées au flux d'une entité");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/entite/1/flux?flux=actes-generique");
        $I->verifyJsonResponseOK(array(array('type' => 'signature')));
    }

    public function associerInstance(NoGuy $I)
    {
        $I->wantTo("associer une instance de connecteur à un flux");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/connecteur?id_connecteur=fakeIparapheur&libelle=test");
        $id_ce = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendPOST("/entite/1/flux/helios-generique/connecteur/$id_ce?type=signature");
        $id_fe = $I->grabDataFromResponseByJsonPath('$.id_fe')[0];
        $I->verifyJsonResponseOK(array('id_fe' => $id_fe), \Codeception\Util\HttpCode::CREATED);
        $I->sendGET("/entite/1/flux?flux=helios-generique");
        $I->verifyJsonResponseOK(array('id_fe' => $id_fe,'id_e' => 1,'flux' => 'helios-generique','type' => 'signature'));
    }

    public function associerInstanceV1(NoGuy $I)
    {
        $I->wantTo("associer une instance de connecteur à un flux [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/connecteur?id_connecteur=fakeIparapheur&libelle=test");
        $id_ce = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendGETV1("create-flux-connecteur.php?id_e=1&flux=helios-generique&id_ce=$id_ce&type=signature");
        $id_fe = $I->grabDataFromResponseByJsonPath('$.id_fe')[0];
        $I->verifyJsonResponseOK(array('id_fe' => $id_fe), \Codeception\Util\HttpCode::OK);
        $I->sendGET("/entite/1/flux?flux=helios-generique");
        $I->verifyJsonResponseOK(array('id_fe' => $id_fe,'id_e' => 1,'flux' => 'helios-generique','type' => 'signature'));
    }

    public function supprimerAssociation(NoGuy $I)
    {
        $I->wantTo("supprimer une association");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/connecteur?id_connecteur=test&libelle=test");
        $id_ce = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendPOST("/entite/1/flux/test/connecteur/$id_ce?type=test");
        $id_fe = $I->grabDataFromResponseByJsonPath('$.id_fe')[0];
        $I->sendDELETE("/entite/1/flux?id_fe=$id_fe");
        $I->verifyJsonResponseOK(array('result' => 'ok'));
    }

    public function supprimerAssociationV1(NoGuy $I)
    {
        $I->wantTo("supprimer une association [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/connecteur?id_connecteur=test&libelle=test");
        $id_ce = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendPOST("/entite/1/flux/test/connecteur/$id_ce?type=test");
        $id_fe = $I->grabDataFromResponseByJsonPath('$.id_fe')[0];
        $I->sendGETV1("delete-flux-connecteur.php?id_e=1&id_fe=$id_fe");
        $I->verifyJsonResponseOK(array('result' => 'ok'));
    }

    public function actionAssociation(NoGuy $I)
    {
        $I->wantTo("supprimer une association [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/entite/1/connecteur?id_connecteur=test&libelle=test");
        $id_ce = $I->grabDataFromResponseByJsonPath('$.id_ce')[0];
        $I->sendPOST("/entite/1/flux/test/connecteur/$id_ce?type=test");
        $I->sendGETV1("action-connecteur-entite.php?id_e=1&type=test&flux=test&action=ok");
        $I->verifyJsonResponseOK(array('result' => 1, "message" => 'OK !'), \Codeception\Util\HttpCode::OK);
    }
}
