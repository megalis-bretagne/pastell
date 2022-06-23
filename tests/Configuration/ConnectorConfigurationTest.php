<?php

namespace Pastell\Tests\Configuration;

use Pastell\Configuration\ConnectorConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ConnectorConfigurationTest extends TestCase
{
    public function testNominalCase(): void
    {
        $config = Yaml::parse(
            file_get_contents(__DIR__ . '/fixtures/nominal-connector.yml')
        );
        $processor = new Processor();
        $connectorConfiguration = new ConnectorConfiguration();
        $processedConfiguration = $processor->processConfiguration(
            $connectorConfiguration,
            [$config]
        );
        self::assertEquals('Nominal Connector', $processedConfiguration['nom']);
    }

    /**
     * @throws \JsonException
     */
    public function testTrueConnector(): void
    {
        $config = Yaml::parse(
            file_get_contents(__DIR__ . '/../../connecteur/as@lae-rest/entite-properties.yml')
        );
        $processor = new Processor();
        $connectorConfiguration = new ConnectorConfiguration();
        $processedConfiguration = $processor->processConfiguration(
            $connectorConfiguration,
            [$config]
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
