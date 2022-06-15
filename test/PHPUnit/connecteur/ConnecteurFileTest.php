<?php

use Pastell\Configuration\ConnectorConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ConnecteurFileTest extends PastellTestCase
{
    /**
     * @dataProvider filesEntitiesProvider
     * @return void
     */
    public function testAllConnecteur($filePath): void
    {
        $processor = new Processor();
        $connectorConfiguration = new ConnectorConfiguration();

        $config = Yaml::parse(
            file_get_contents($filePath)
        );
        $processedConfiguration = $processor->processConfiguration(
            $connectorConfiguration,
            [$config]
        );
        self::assertNotEmpty($processedConfiguration);
    }

    public function filesEntitiesProvider(): array
    {
        $provider = [];
        $connecteurDefintionFiles = $this->getObjectInstancier()->getInstance(ConnecteurDefinitionFiles::class);
        $allEntitiesFiles = $connecteurDefintionFiles->getAllDefinitionPath(
            ConnecteurDefinitionFiles::ENTITE_PROPERTIES_FILENAME
        );
        foreach ($allEntitiesFiles as $connecteurId => $filePath) {
            $provider[$connecteurId] = [$filePath];
        }
        $allEntitiesFiles = $connecteurDefintionFiles->getAllDefinitionPath(
            ConnecteurDefinitionFiles::GLOBAL_PROPERTIES_FILENAME
        );
        foreach ($allEntitiesFiles as $connecteurId => $filePath) {
            $provider[$connecteurId . ' (global)'] = [$filePath];
        }
        return $provider;
    }
}
