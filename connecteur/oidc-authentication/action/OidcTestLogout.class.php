<?php

class OidcTestLogout extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var OidcAuthentication $oidc */
        $oidc = $this->getMyConnecteur();
        $oidc->logout(SITE_BASE);
        $this->setLastMessage('Déconnecté avec succès');
        return true;
    }
}
