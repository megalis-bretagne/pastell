<?php

class DepotTestEcritureFichier extends ActionExecutor
{
    public function go()
    {
        /** @var DepotConnecteur $depotConnecteur */
        $depotConnecteur = $this->getMyConnecteur();
        $result = $depotConnecteur->testEcritureFichier();
        $this->setLastMessage("Dépot du fichier sur : $result");
        return true;
    }
}
