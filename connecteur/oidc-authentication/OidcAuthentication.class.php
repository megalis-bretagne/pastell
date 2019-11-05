<?php

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;

class OidcAuthentication extends AuthenticationConnecteur
{

    private $providerUrl;
    private $clientId;
    private $clientSecret;
    private $loginAttribute;

    /** @var OpenIDConnectClient */
    private $oidc;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->providerUrl = $donneesFormulaire->get('provider_url');
        $this->clientId = $donneesFormulaire->get('client_id');
        $this->clientSecret = $donneesFormulaire->get('client_secret');
        $this->loginAttribute = $donneesFormulaire->get('login_attribute');

        $this->oidc = new OpenIDConnectClient(
            $this->providerUrl,
            $this->clientId,
            $this->clientSecret
        );
    }

    /**
     * @param $redirectUrl
     * @return mixed
     * @throws OpenIDConnectClientException
     */
    public function authenticate($redirectUrl = false)
    {
        if ($redirectUrl) {
            $this->oidc->setRedirectURL($redirectUrl);
        }

        $this->oidc->authenticate();
        return $this->oidc->requestUserInfo($this->loginAttribute);
    }

    public function logout($redirectUrl = false)
    {
        $this->oidc->signOut($this->oidc->getIdToken(), $redirectUrl ?: SITE_BASE);
    }

    public function getExternalSystemName(): string
    {
        return 'OIDC';
    }
}
