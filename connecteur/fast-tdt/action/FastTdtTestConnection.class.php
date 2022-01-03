<?php

class FastTdtTestConnection extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go()
    {
        /** @var FastTdt $connecteur */
        $connecteur = $this->getMyConnecteur();

        $connecteur->testConnexion();

        $this->setLastMessage("La connexion est réussie");

        return true;
    }
}
