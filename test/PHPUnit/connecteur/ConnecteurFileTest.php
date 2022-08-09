<?php

use Pastell\Configuration\ConnectorValidation;

class ConnecteurFileTest extends PastellTestCase
{
    /**
     * @dataProvider filesEntitiesProvider
     * @return void
     */
    public function testAllConnecteur(string $filePath): void
    {
        $connectorValidation = $this->getObjectInstancier()->getInstance(ConnectorValidation::class);
        self::assertNotEmpty($connectorValidation->getConfiguration($filePath));
    }

    public function filesEntitiesProvider(): array
    {
        $provider = [];
        $connecteurDefinitionFiles = $this->getObjectInstancier()->getInstance(ConnecteurDefinitionFiles::class);
        $allEntitiesFiles = $connecteurDefinitionFiles->getAllDefinitionPath(
            ConnecteurDefinitionFiles::ENTITE_PROPERTIES_FILENAME
        );
        foreach ($allEntitiesFiles as $connecteurId => $filePath) {
            $provider[$connecteurId] = [$filePath];
        }
        $allEntitiesFiles = $connecteurDefinitionFiles->getAllDefinitionPath(
            ConnecteurDefinitionFiles::GLOBAL_PROPERTIES_FILENAME
        );
        foreach ($allEntitiesFiles as $connecteurId => $filePath) {
            $provider[$connecteurId . ' (global)'] = [$filePath];
        }
        return $provider;
    }
}
