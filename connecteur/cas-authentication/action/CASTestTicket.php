<?php

class CASTestTicket extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var CASAuthentication $cas */
        $cas = $this->getMyConnecteur();
        $login = $cas->authenticate(SITE_BASE . "/Connexion/externalAuthentication?id_ce={$this->id_ce}");
        if (!$login) {
            $this->setLastMessage("Aucune session en cours");
            return false;
        }
        $this->setLastMessage("Authentifié avec le login : $login");
        return true;
    }
}
