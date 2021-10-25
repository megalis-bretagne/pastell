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
        $this->assertSame(
            "aface590 bd16b46e 07cfc45c bf20f81a ad82815b 6ac9e02f 3a5092d6 fe446c18",
            $this->getTypeDossierManager()->getHash($id_t)
        );

        $typeDossierEditionService->editLibelleInfo(
            $id_t,
            "arrete-rh",
            "Flux CD 99",
            "Ceci est un flux de test",
            "Information"
        );
        $this->assertSame("9473ca01 56f6a42c ae179004 06a0d647 309b873c 53d971e2 7d9d41e0 e721e4fb", $this->getTypeDossierManager()->getHash($id_t));
    }
}
