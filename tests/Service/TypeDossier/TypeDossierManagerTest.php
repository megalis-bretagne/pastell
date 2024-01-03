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
            "63c6fd80 722bd575 63a30828 70ec160a b74176d8 3d59f1b0 e7bfd742 28251e82",
            $this->getTypeDossierManager()->getHash($id_t)
        );

        $typeDossierEditionService->editLibelleInfo(
            $id_t,
            "arrete-rh",
            "Flux CD 99",
            "Ceci est un flux de test",
            "Information"
        );
        $this->assertSame("23f2132f a957689b 99c41e59 5219f2ca 06181e14 da255985 4a5b1119 dd477cae", $this->getTypeDossierManager()->getHash($id_t));
    }
}
