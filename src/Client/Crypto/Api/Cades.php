<?php

namespace Pastell\Client\Crypto\Api;

use Pastell\Client\Crypto\CryptoClient;
use Pastell\Client\Crypto\CryptoClientException;
use Psr\Http\Client\ClientExceptionInterface;

class Cades
{
    public const CADES_GENERATE_DATA_TO_SIGN = '/crypto/api/v2/cades/generateDataToSign';
    public const CADES_GENERATE_SIGNATURE = '/crypto/api/v2/cades/generateSignature';

    /**
     * @var CryptoClient
     */
    private $client;

    public function __construct(CryptoClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CryptoClientException
     */
    public function generateDataToSign(string $filepath, string $publicCertificate): string
    {
        $stream = $this->client->getMultipartStream(
            $filepath,
            [
                'publicCertificateBase64' => $publicCertificate,
            ]
        );
        return $this->client->post(self::CADES_GENERATE_DATA_TO_SIGN, $stream);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CryptoClientException
     */
    public function generateSignature(
        string $filepath,
        string $publicCertificate,
        array $dataToSignList,
        string $signatureDateTime
    ): string {
        $stream = $this->client->getMultipartStream(
            $filepath,
            [
                'publicCertificateBase64' => $publicCertificate,
                'dataToSignList' => $dataToSignList,
                'signatureDateTime' => $signatureDateTime,
            ]
        );

        return $this->client->post(self::CADES_GENERATE_SIGNATURE, $stream);
    }
}
