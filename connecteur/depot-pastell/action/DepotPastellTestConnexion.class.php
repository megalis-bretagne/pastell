<?php

class DepotPastellTestConnexion extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var DepotPastell $pastellConnecteur */
        $pastellConnecteur = $this->getMyConnecteur();
        $version = $pastellConnecteur->getVersion();
        $this->setLastMessage("Version de Pastell: $version");
        return true;
    }
}
