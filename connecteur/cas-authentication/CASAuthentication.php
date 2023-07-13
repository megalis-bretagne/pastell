<?php

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

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
            // TODO v5: Have a checkbox in connector to active debugging and log into pastell.log
            $logger = new Logger('CAS');
            $logger->pushHandler(new StreamHandler($cas_debug, Level::Info));
            phpCAS::setLogger($logger);
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

    /**
     * @deprecated 4.0.6, unused, to be removed in v5
     */
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
