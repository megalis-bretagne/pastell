<?php

class OidcTestLogout extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $oidc = $this->getMyConnecteur();
        $oidc->logout(SITE_BASE);
        $this->setLastMessage('Déconnecté avec succès');
        return true;
    }
}
