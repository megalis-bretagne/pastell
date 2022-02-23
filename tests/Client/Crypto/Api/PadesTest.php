<?php

namespace Pastell\Tests\Client\Crypto\Api;

use GuzzleHttp\Psr7\Response;
use Pastell\Client\Crypto\Api\Pades;
use Pastell\Client\Crypto\CryptoClient;
use Pastell\Client\Crypto\CryptoClientException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

use function file_get_contents;
use function json_decode;
use function json_encode;

class PadesTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $clientInterface;

    /**
     * @var Pades
     */
    private $padesApi;

    protected function setUp(): void
    {
        $this->clientInterface = $this->getMockBuilder(ClientInterface::class)->getMock();
        $this->padesApi = new Pades(new CryptoClient($this->clientInterface));
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
            $this->padesApi->generateDataToSign(
                __DIR__ . '/../../../../test/PHPUnit/fixtures/vide.pdf',
                'publicCertificate',
                json_decode(file_get_contents(__DIR__ . '/../fixtures/defaultStamp.json'), true)
            )
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CryptoClientException
     */
    public function testGenerateSignature(): void
    {
        $expected = '%PDF-1.3';

        $this->clientInterface
            ->method('sendRequest')
            ->willReturn(new Response(200, [], $expected));

        $this->assertSame(
            $expected,
            $this->padesApi->generateSignature(
                __DIR__ . '/../../../../test/PHPUnit/fixtures/vide.pdf',
                'publicCertificate',
                [
                    [
                        'id' => 'filename.pdf',
                        'digestBase64' => null,
                        'dataToSignBase64' => '__DATA_TO_SIGN_BASE64__',
                        'signatureValue' => '__SIGNATURE_VALUE__'
                    ],
                ],
                1631544860572,
                json_decode(file_get_contents(__DIR__ . '/../fixtures/defaultStamp.json'), true)
            )
        );
    }
}
