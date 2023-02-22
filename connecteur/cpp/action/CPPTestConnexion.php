<?php

class CPPTestConnexion extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go(): bool
    {
        /** @var CPP $cpp */
        $cpp = $this->getMyConnecteur();
        if (! $cpp->testConnexion()) {
            $this->setLastMessage("La connexion cpp a échoué : " . $cpp->getLastError());
            return false;
        }
        $this->setLastMessage("La connexion est réussie");
        return true;
    }
}
