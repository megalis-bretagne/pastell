<?php

class CASTestLogout extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var CASAuthentication $cas */
        $cas = $this->getMyConnecteur();
        $cas->logout($this->getSiteBase());
        $this->setLastMessage("Déconnecté avec succès");
        return true;
    }
}
