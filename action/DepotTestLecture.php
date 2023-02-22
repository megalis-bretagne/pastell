<?php

class DepotTestLecture extends ConnecteurTypeActionExecutor
{
    public function go()
    {
        /** @var DepotConnecteur $depotConnecteur */
        $depotConnecteur = $this->getMyConnecteur();
        $result = $depotConnecteur->testLecture();
        $this->setLastMessage($result);
        return true;
    }
}
