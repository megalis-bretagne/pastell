<?php

namespace Pastell\Tests\Client\Crypto\Api;

use GuzzleHttp\Psr7\Response;
use Pastell\Client\Crypto\Api\Xades;
use Pastell\Client\Crypto\CryptoClient;
use Pastell\Client\Crypto\CryptoClientException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

use function json_encode;

class XadesTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $clientInterface;

    /**
     * @var Xades
     */
    private $xadesApi;

    protected function setUp(): void
    {
        $this->clientInterface = $this->getMockBuilder(ClientInterface::class)->getMock();
        $this->xadesApi = new Xades(new CryptoClient($this->clientInterface));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CryptoClientException
     */
    public function testGenerateDataToSign(): void
    {
        $expected = json_encode([
            'dataToSignList' => [
                [
                    'id' => 'filename.pdf',
                    'digestBase64' => null,
                    'dataToSignBase64' => '__DATA_TO_SIGN_BASE64__',
                    'signatureValue' => null
                ],
            ],
            'signatureDateTime' => 1631544860572
        ]);

        $this->clientInterface
            ->method('sendRequest')
            ->willReturn(new Response(200, [], $expected));

        $this->assertSame(
            $expected,
            $this->xadesApi->generateDataToSign(
                __DIR__ . '/../../../../test/PHPUnit/fixtures/vide.pdf',
                'publicCertificate',
                [
                    'country' => 'France',
                    'city' => 'Montpellier',
                    'zipCode' => '34000',
                    'claimedRoles' => [
                        'Ordonnateur'
                    ]
                ]
            )
        );
    }

    /**
     * @throws CryptoClientException
     * @throws ClientExceptionInterface
     */
    public function testGenerateSignature(): void
    {
        $expected = '<?xml version="1.0" encoding="ISO-8859-1"?><n:PES_Aller></n:PES_Aller>';

        $this->clientInterface
            ->method('sendRequest')
            ->willReturn(new Response(200, [], $expected));

        $this->assertSame(
            $expected,
            $this->xadesApi->generateSignature(
                __DIR__ . '/../../../../test/PHPUnit/module/helios-generique/fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml',
                'publicCertificate',
                [
                    [
                        'id' => 'filename.pdf',
                        'digestBase64' => null,
                        'dataToSignBase64' => '__DATA_TO_SIGN_BASE64__',
                        'signatureValue' => '__SIGNATURE_VALUE__'
                    ],
                ],
                '1631544860572',
                [
                    'country' => 'France',
                    'city' => 'Montpellier',
                    'zipCode' => '34000',
                    'claimedRoles' => [
                        'Ordonnateur'
                    ]
                ]
            )
        );
    }
}
