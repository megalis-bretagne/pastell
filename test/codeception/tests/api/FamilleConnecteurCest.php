<?php

class FamilleConnecteurCest
{
    public function listFamilleConnecteur(NoGuy $I)
    {
        $I->wantTo("lister les familles de connecteurs");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/familleConnecteur");
        $I->verifyJsonResponseOK(array('Bordereau SEDA'));
    }

    public function listFamilleConnecteurGlobal(NoGuy $I)
    {
        $I->wantTo("lister les familles de connecteurs globaux");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/familleConnecteur?global=true");
        $I->verifyJsonResponseOK(array('Authentification'));
    }

    public function listConnecteur(NoGuy $I)
    {
        $I->wantTo("lister les connecteurs d'une famille");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/familleConnecteur/TdT");
        $I->verifyJsonResponseOK(array('s2low','fakeTdt'));
    }

    public function listConnecteurGlobal(NoGuy $I)
    {
        $I->wantTo("lister les connecteurs globaux d'une famille");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/familleConnecteur/Authentification?global=true");
        $I->verifyJsonResponseOK(array('cas-authentication'));
    }
}
