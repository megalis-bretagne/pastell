<?php

namespace Pastell\Client\Crypto\Api;

use Pastell\Client\Crypto\CryptoClient;
use Pastell\Client\Crypto\CryptoClientException;
use Psr\Http\Client\ClientExceptionInterface;

class Xades
{
    private const XADES_GENERATE_DATA_TO_SIGN = '/crypto/api/v2/xades/generateDataToSign';
    private const XADES_GENERATE_SIGNATURE = '/crypto/api/v2/xades/generateSignature';

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
    public function generateDataToSign(
        string $filepath,
        string $publicCertificate,
        array $payload,
        string $signaturePackaging = 'ENVELOPED',
        string $xpath = ''
    ): string {
        $stream = $this->client->getMultipartStream(
            $filepath,
            [
                'publicCertificateBase64' => $publicCertificate,
                'country' => $payload['country'],
                'city' => $payload['city'],
                'zipCode' => $payload['zipCode'],
                'claimedRoles' => $payload['claimedRoles'],
                'signaturePackaging' => $signaturePackaging,
                'xpath' => $xpath,
            ]
        );
        return $this->client->post(self::XADES_GENERATE_DATA_TO_SIGN, $stream);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CryptoClientException
     */
    public function generateSignature(
        string $filepath,
        string $publicCertificate,
        array $dataToSignList,
        string $signatureDateTime,
        array $payload = [],
        string $signaturePackaging = 'ENVELOPED',
        string $xpath = ''
    ): string {
        $stream = $this->client->getMultipartStream(
            $filepath,
            [
                'publicCertificateBase64' => $publicCertificate,
                'dataToSignList' => $dataToSignList,
                'signatureDateTime' => $signatureDateTime,
                'country' => $payload['country'],
                'city' => $payload['city'],
                'zipCode' => $payload['zipCode'],
                'claimedRoles' => $payload['claimedRoles'],
                'signaturePackaging' => $signaturePackaging,
                'xpath' => $xpath,
            ]
        );

        return $this->client->post(self::XADES_GENERATE_SIGNATURE, $stream);
    }
}
