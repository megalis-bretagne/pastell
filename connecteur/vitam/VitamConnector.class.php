<?php

declare(strict_types=1);

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use VitamClient\Client;

final class VitamConnector extends SAEConnecteur
{
    private Client $client;
    private int $tenant;
    private string $context;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->tenant = (int)$donneesFormulaire->get('tenant');
        $this->context = $donneesFormulaire->get('workflow');
        $url = $donneesFormulaire->get('url');

        $cert = $donneesFormulaire->getFilePath('certificate_pem');
        $key = $donneesFormulaire->getFilePath('certificate_key');
        $passphrase = $donneesFormulaire->get('certificate_password');
        $psr18Client = new Psr18Client(
            HttpClient::createForBaseUri(
                $url,
                [
                    'verify_peer' => false,
                    'verify_host' => false,
                    'local_cert' => $cert,
                    'local_pk' => $key,
                    'passphrase' => $passphrase,
                ]
            )
        );
        $this->client = Client::createWithHttpClient($psr18Client, $url);
    }

    /**
     * @throws \Http\Client\Exception
     * @throws \JsonException
     */
    public function sendSIP(
        string $bordereau,
        string $archivePath
    ): string {
        return $this->client->ingest()->create(
            $this->tenant,
            $archivePath,
            $this->context,
        );
    }

    public function provideAcknowledgment(): bool
    {
        return false;
    }

    public function getAck(string $transfertId, string $originatingAgencyId): string
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @throws \Http\Client\Exception
     */
    public function getAtr(string $transfertId, string $originatingAgencyId): string
    {
        return $this->client->ingest()->getAtr($this->tenant, $transfertId);
    }
}
