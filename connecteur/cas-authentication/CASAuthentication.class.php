<?php

final class CASAuthentication extends AuthenticationConnecteur
{
    private $host;
    private $port;
    private $context;
    private $ca_file;
    private $proxy;
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
        $this->host = $donneesFormulaire->get('cas_host');
        $this->port = $donneesFormulaire->get('cas_port');
        $this->context = $donneesFormulaire->get('cas_context');
        $this->ca_file = $donneesFormulaire->getFilePath('cas_ca');
        $this->proxy = $donneesFormulaire->get('cas_proxy');
        $cas_debug = $donneesFormulaire->get('cas_debug');
        if ($cas_debug) {
            phpCAS::setDebug($cas_debug);
        }
    }

    private function setClient(): void
    {
        phpCAS::client(CAS_VERSION_2_0, $this->host, (int)$this->port, $this->context, $this->site_base);
        phpCAS::setCasServerCACert($this->ca_file);
        if ($this->proxy) {
            phpCAS::allowProxyChain(new CAS_ProxyChain([$this->proxy]));
        }
    }

    public function isSessionAuthenticated()
    {
        $this->setClient();
        if (phpCAS::isSessionAuthenticated()) {
            phpCAS::handleLogoutRequests(false);
            phpCAS::forceAuthentication();
            return phpCAS::getUser();
        }
        return false;
    }

    public function testAuthenticate(string $redirectUrl)
    {
        return $this->authenticate($redirectUrl);
    }

    public function authenticate($url = false)
    {
        $this->setClient();
        if ($url) {
            phpCAS::setFixedServiceURL($url);
        }
        phpCAS::handleLogoutRequests(false);
        phpCAS::forceAuthentication();
        return phpCAS::getUser();
    }

    public function logout($redirectUrl = false)
    {
        $this->setClient();
        phpCAS::logout();
    }

    public function getExternalSystemName(): string
    {
        return 'CAS';
    }

    public function getLogoutRedirectUrl(): string
    {
        return '';
    }
}
