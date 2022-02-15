<?php

namespace Pastell\Tests\Client\Crypto\Api;

use GuzzleHttp\Psr7\Response;
use Pastell\Client\Crypto\Api\Version;
use Pastell\Client\Crypto\CryptoClient;
use Pastell\Client\Crypto\CryptoClientException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

use function json_encode;

class VersionTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $clientInterface;

    /**
     * @var Version
     */
    private $versionApi;

    protected function setUp()
    {
        $this->clientInterface = $this->getMockBuilder(ClientInterface::class)->getMock();
        $this->versionApi = new Version(new CryptoClient($this->clientInterface));
    }

    /**
     * @throws CryptoClientException
     * @throws ClientExceptionInterface
     */
    public function testGetVersion(): void
    {
        $expected = json_encode([
            'application' => [
                'name' => 'Crypto',
                'author' => [
                    'name' => 'Libriciel SCOP'
                ]
            ],
            'build' => [
                'artifact' => 'crypto',
                'name' => 'crypto',
                'time' => '2021-10-28T16:48:51.533Z',
                'version' => '2.3.1',
                'group' => 'coop.libriciel'
            ]
        ]);

        $this->clientInterface
            ->method('sendRequest')
            ->willReturn(new Response(200, [], $expected));

        $this->assertSame(
            $expected,
            $this->versionApi->getVersion()
        );
    }
}
