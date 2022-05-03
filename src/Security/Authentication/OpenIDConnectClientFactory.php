<?php

declare(strict_types=1);

namespace Pastell\Security\Authentication;

use Jumbojett\OpenIDConnectClient;

class OpenIDConnectClientFactory
{
    public function getInstance(string $providerUrl, string $clientId, string $clientSecret): OpenIDConnectClient
    {
        return new OpenIDConnectClient($providerUrl, $clientId, $clientSecret);
    }
}
