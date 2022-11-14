<?php

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;

final class OidcAuthentication extends AuthenticationConnecteur
{
    private const OIDC_REDIRECT_URI = '/Connexion/oidc';

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
     * @throws OpenIDConnectClientException
     */
    public function authenticate($redirectUrl = false)
    {
        return $this->testAuthenticate(\rtrim($this->site_base, '/') . self::OIDC_REDIRECT_URI);
    }

    /**
     * @throws OpenIDConnectClientException
     */
    public function testAuthenticate(string $redirectUrl)
    {
        $this->oidc->setRedirectURL($redirectUrl);
        $this->oidc->authenticate();
        return $this->oidc->requestUserInfo($this->loginAttribute);
    }

    public function logout($redirectUrl = false)
    {
        $this->oidc->signOut($this->oidc->getIdToken(), $redirectUrl ?: $this->site_base);
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
