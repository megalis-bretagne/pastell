<?php

namespace Pastell\Client\Crypto;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class CryptoClientFactory
{
    public function getClient(string $url): CryptoClient
    {
        $client = new Psr18Client(HttpClient::createForBaseUri($url));
        return new CryptoClient($client);
    }
}
