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
            "1abe7dc4 5775846e 42c0f73f 70d9d252 4a203c61 ef3ab2d9 5dbc36d3 6c156430",
            $this->getTypeDossierManager()->getHash($id_t)
        );

        $typeDossierEditionService->editLibelleInfo(
            $id_t,
            "arrete-rh",
            "Flux CD 99",
            "Ceci est un flux de test",
            "Information"
        );
        $this->assertSame("f8675184 a6cc5f2f fc48316a 73ee2889 a7fe7c8f acf5309e 7bc29dcd 3bfa8ebb", $this->getTypeDossierManager()->getHash($id_t));
    }
}
