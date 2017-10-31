<?php

class DepotTestEcriture extends ConnecteurTypeActionExecutor {

    public function go(){
        /** @var DepotConnecteur $depotConnecteur */
        $depotConnecteur = $this->getMyConnecteur();
        $result = $depotConnecteur->testEcriture();
        $this->setLastMessage("Création d'un répertoire et dépot du fichier $result");
        return true;
    }

}