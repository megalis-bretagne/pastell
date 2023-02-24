<?php

class FastParapheurTestConnection extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go()
    {
        /** @var FastParapheur $connecteur */
        $connecteur = $this->getMyConnecteur();

        $connecteur->testConnection();

        $this->setLastMessage("La connexion est réussie");
        return true;
    }
}
