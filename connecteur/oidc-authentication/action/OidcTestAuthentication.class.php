<?php

class OidcTestAuthentication extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var OidcAuthentication $oidc */
        $oidc = $this->getMyConnecteur();
        $userInfo = $oidc->getConnectedUserInfo(
            SITE_BASE . "/Connexion/externalOIDCInfo?id_ce={$this->id_ce}"
        );
        if (!$userInfo) {
            $this->setLastMessage('Aucune session en cours');
            return false;
        }
        $this->setLastMessage('Authentifié avec les informations suivantes : ' . implode("<br/>", $userInfo));
        return true;
    }
}
