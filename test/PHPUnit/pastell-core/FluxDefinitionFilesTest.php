<?php

use Pastell\Service\Pack\PackService;

class FluxDefinitionFilesTest extends PastellTestCase
{
    /**
     * @return FluxDefinitionFiles
     */
    private function getFluxDefinitionFiles()
    {
        $ymlLoader = new YMLLoader(new MemoryCacheNone());
        $fluxDefinitionFiles = new FluxDefinitionFiles(
            $this->getObjectInstancier()->Extensions,
            $ymlLoader,
            $this->getObjectInstancier()->getInstance(PackService::class),
            new MemoryCacheNone(),
            10
        );
        return $fluxDefinitionFiles;
    }

    public function testGetAll()
    {
        $flux_list = $this->getFluxDefinitionFiles()->getAll();
        $this->assertNotEmpty($flux_list);
    }

    public function testGetInfo()
    {
        $flux_info = $this->getFluxDefinitionFiles()->getInfo('mailsec');
        $this->assertEquals("Mail sécurisé", $flux_info['nom']);
    }
}
