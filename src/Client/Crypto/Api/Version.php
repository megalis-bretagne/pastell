<?php

namespace Pastell\Client\Crypto\Api;

use Pastell\Client\Crypto\CryptoClient;
use Pastell\Client\Crypto\CryptoClientException;
use Psr\Http\Client\ClientExceptionInterface;

final class Version
{
    public const VERSION = '/crypto/actuator/info';

    /**
     * @var CryptoClient
     */
    private $client;

    public function __construct(CryptoClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws CryptoClientException
     * @throws ClientExceptionInterface
     */
    public function getVersion(): string
    {
        return $this->client->get(self::VERSION);
    }
}
