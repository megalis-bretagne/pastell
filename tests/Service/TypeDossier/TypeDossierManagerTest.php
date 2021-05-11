<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierManager;
use Pastell\Service\TypeDossier\TypeDossierUtilService;
use Pastell\Service\TypeDossier\TypeDossierEditionService;
use PastellTestCase;
use TypeDossierException;
use TypeDossierProperties;

class TypeDossierManagerTest extends PastellTestCase
{
    private function getTypeDossierManager()
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierManager::class);
    }

    public function testGetEmptyDossierData()
    {
        $typeDossierData42 = $this->getTypeDossierManager()->getTypeDossierProperties(42);
        $typeDossierData = new TypeDossierProperties();
        $this->assertEquals($typeDossierData, $typeDossierData42);
    }

    public function testGetTypeDossierFromArray()
    {
        $json_content = json_decode(file_get_contents(TypeDossierImportServiceTest::FIXTURE_FILE), true);
        $typeDossierProperties = $this->getTypeDossierManager()->getTypeDossierFromArray($json_content[TypeDossierUtilService::RAW_DATA]);
        $this->assertEquals(TypeDossierImportServiceTest::ID_TYPE_DOSSIER, $typeDossierProperties->id_type_dossier);
    }

    /**
     * @throws TypeDossierException
     */
    public function testGetHash()
    {
        $typeDossierProperties = new TypeDossierProperties();
        $typeDossierProperties->id_type_dossier = "arrete-rh";
        $typeDossierEditionService = $this->getObjectInstancier()->getInstance(TypeDossierEditionService::class);
        $id_t = $typeDossierEditionService->create($typeDossierProperties);

        $this->assertSame("f843dc3a09d55a863a6f16819f6dc56b17788a37705e1cc1b648896e7795e4bd", $this->getTypeDossierManager()->getHash($id_t));

        $typeDossierEditionService->editLibelleInfo(
            $id_t,
            "arrete-rh",
            "Flux CD 99",
            "Ceci est un flux de test",
            "Information"
        );
        $this->assertSame("8a206ca18c10e7b5a49e75fab5a5f84cb9cfbdaf8b6d8870966ffccc521692d4", $this->getTypeDossierManager()->getHash($id_t));
    }
}
