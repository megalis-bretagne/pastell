<?php

namespace Pastell\Client\IparapheurV5;

use IparapheurV5Client\Client;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\Psr18Client;

class ClientFactory
{
    private ClientInterface $clientInterface;
    public function setClientInterface(ClientInterface $clientInterface): void
    {
        $this->clientInterface = $clientInterface;
    }
    public function getInstance(): Client
    {
        if (isset($this->clientInterface)) {
            return Client::createWithHttpClient($this->clientInterface);
        }
        return Client::createWithHttpClient(new Psr18Client());
    }
}
