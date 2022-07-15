<?php

namespace Pastell\Tests\Configuration;

use Pastell\Configuration\ConnectorConfiguration;
use Pastell\Configuration\ConnectorValidation;
use PastellTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ConnectorConfigurationTest extends PastellTestCase
{
    public function testNominalCase(): void
    {
        $connectorValidation = $this->getObjectInstancier()->getInstance(ConnectorValidation::class);
        $processedConfiguration = $connectorValidation->getConfiguration(
            __DIR__ . '/fixtures/nominal-connector.yml'
        );
        self::assertEquals('Nominal Connector', $processedConfiguration['nom']);
    }

    /**
     * @throws \JsonException
     */
    public function testTrueConnector(): void
    {
        $connectorValidation = $this->getObjectInstancier()->getInstance(ConnectorValidation::class);
        $processedConfiguration = $connectorValidation->getConfiguration(
            __DIR__ . '/../../connecteur/as@lae-rest/entite-properties.yml'
        );
        self::assertEquals('As@lae (Rest)', $processedConfiguration['nom']);
        /*file_put_contents(
            __DIR__ . '/fixtures/processedConfiguration.json',
            json_encode($processedConfiguration, JSON_THROW_ON_ERROR)
        );*/
        self::assertJsonStringEqualsJsonFile(
            __DIR__ . '/fixtures/processedConfiguration.json',
            json_encode($processedConfiguration, JSON_THROW_ON_ERROR)
        );
    }
}
