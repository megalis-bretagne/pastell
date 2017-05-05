<?php

class SystemFluxCest extends PastellCest {

    public function listTestFlux(AcceptanceTester $I){
        $I->wantTo("voir que tous les flux disponibles sont valides");
        $I->am("admin");
        $I->amOnPage("/System/flux");
        $I->see("Flux disponibles");
        $I->dontSee("Erreur sur le flux !");
    }

}
