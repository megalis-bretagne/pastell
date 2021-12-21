<?php

class OidcTestAuthentication extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $oidc = $this->getMyConnecteur();
        $login = $oidc->authenticate(SITE_BASE . "/Connexion/externalAuthentication?id_ce={$this->id_ce}");

        if (!$login) {
            $this->setLastMessage('Aucune session en cours');
            return false;
        }
        $this->setLastMessage("Authentifié avec le login : $login");
        return true;
    }
}
