<?php

class TypeDossierEtapeManagerTest extends PastellTestCase
{
    private function getTypeDossierEtapeManager()
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);
    }

    public function testGetAllType()
    {
        $all_type = $this->getTypeDossierEtapeManager()->getAllType();
        $this->assertContains('Dépôt (GED, FTP, ...)', $all_type);
        $this->assertArrayNotHasKey("type-dossier-starter-kit.yml", $all_type);
    }

    public function testGetLibelle()
    {
        $this->assertEquals(
            'Dépôt (GED, FTP, ...)',
            $this->getTypeDossierEtapeManager()->getLibelle('depot')
        );
    }

    public function testSetSpecificEtapeProperties()
    {
        $etape = new TypeDossierEtapeProperties();
        $etape->type = 'sae';
        $result = ['formulaire' => ['Configuration SAE' => ['element1' => []]],'action' => []];
        $result = $this->getTypeDossierEtapeManager()->setSpecificData($etape, $result);
        $this->assertEquals([
            'formulaire' => [],
            'action' => ['supression' => ['rule' => ['last-action' => ['rejet-sae']]]]
        ], $result);
    }

    public function testSpecificEtapeWhenHasExtensions()
    {
        $redisWrapper = $this->getObjectInstancier()->getInstance(MemoryCache::class);
        $redisWrapper->flushAll();

        $extensionsLoader = $this->getObjectInstancier()->getInstance(ExtensionLoader::class);
        $extensionsLoader->loadExtension([__DIR__ . "/fixtures/extension_test/"]);

        $this->assertEquals(
            'HAL 9000',
            $this->getTypeDossierEtapeManager()->getLibelle('hal-9000')
        );

        $redisWrapper = $this->getObjectInstancier()->getInstance(MemoryCache::class);
        $redisWrapper->flushAll();
    }

    public function testEtapeDoesNotExists()
    {
        $etape = new TypeDossierEtapeProperties();
        $etape->type = 'foo';
        $initial_result = ['formulaire' => [],'action' => []];
        $result = $this->getTypeDossierEtapeManager()->setSpecificData($etape, $initial_result);
        $this->assertEquals($initial_result, $result);
    }
}
