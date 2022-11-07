<?php

final class OidcTestAuthentication extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var OidcAuthentication $oidc */
        $oidc = $this->getMyConnecteur();

        $uri = '/Connexion/externalAuthentication?id_ce=' . $this->id_ce;
        $_SESSION[ConnexionControler::OIDC_URI_REDIRECT_SESSION] = $uri;
        $login = $oidc->authenticate(SITE_BASE . $uri);

        if (!$login) {
            $this->setLastMessage('Aucune session en cours');
            return false;
        }
        $this->setLastMessage("Authentifié avec le login : $login");
        return true;
    }
}
