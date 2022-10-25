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

    /**
     * @var string
     */
    private $redirectUrl;
    /**
     * @var string
     */
    private $site_base;

    public function __construct(string $site_base)
    {
        $this->site_base = $site_base;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->providerUrl = $donneesFormulaire->get('provider_url');
        $this->clientId = $donneesFormulaire->get('client_id');
        $this->clientSecret = $donneesFormulaire->get('client_secret');
        $this->loginAttribute = $donneesFormulaire->get('login_attribute');
        $this->redirectUrl = $donneesFormulaire->get('redirect_url', '');

        $this->oidc = new OpenIDConnectClient(
            $this->providerUrl,
            $this->clientId,
            $this->clientSecret
        );

        if ($donneesFormulaire->get('http_proxy')) {
            $this->oidc->setHttpProxy($donneesFormulaire->get('http_proxy'));
        }
    }

    /**
     * @param $redirectUrl
     * @return mixed
     * @throws OpenIDConnectClientException
     */
    public function authenticate($redirectUrl = false)
    {
        $this->oidc->setRedirectURL(\rtrim($this->site_base, '/') . '/Connexion/oidc');
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

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
