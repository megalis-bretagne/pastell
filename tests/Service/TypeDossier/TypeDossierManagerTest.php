<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierActionService;
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
        $typeDossierActionService = $this->getObjectInstancier()->getInstance(TypeDossierActionService::class);
        $typeDossierActionService->setId_u(0);
        $typeDossierEditionService = $this->getObjectInstancier()->getInstance(TypeDossierEditionService::class);
        $id_t = $typeDossierEditionService->create($typeDossierProperties);

        $this->assertSame("f843dc3a 09d55a86 3a6f1681 9f6dc56b 17788a37 705e1cc1 b648896e 7795e4bd", $this->getTypeDossierManager()->getHash($id_t));

        $typeDossierEditionService->editLibelleInfo(
            $id_t,
            "arrete-rh",
            "Flux CD 99",
            "Ceci est un flux de test",
            "Information"
        );
        $this->assertSame("8a206ca1 8c10e7b5 a49e75fa b5a5f84c b9cfbdaf 8b6d8870 966ffccc 521692d4", $this->getTypeDossierManager()->getHash($id_t));
    }
}
