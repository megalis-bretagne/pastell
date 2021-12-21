<?php

class TestConnexion extends ActionExecutor
{
    public function go()
    {
        /** @var S2low $s2low */
        $s2low = $this->getMyConnecteur();
        $s2low->testConnexion();
        $this->setLastMessage("La connexion est réussie");
        return true;
    }
}
