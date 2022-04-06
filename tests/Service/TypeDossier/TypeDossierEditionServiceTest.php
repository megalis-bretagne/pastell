<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierEditionService;
use Pastell\Service\TypeDossier\TypeDossierManager;
use PastellTestCase;
use TypeDossierException;
use TypeDossierProperties;

class TypeDossierEditionServiceTest extends PastellTestCase
{
    private function getTypeDossierEditionService()
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierEditionService::class);
    }

    public function typeDossierIdProvider()
    {
        return [
            'Null' =>
                [
                    "",
                    "Aucun identifiant de type de dossier fourni",
                ],
            'ExistOnPastell' =>
                [
                    "actes-generique",
                    "Le type de dossier actes-generique existe déjà sur ce Pastell",
                ],
            'Pastell-' =>
                [
                    "pastell-test",
                    "L'identifiant du type de dossier ne doit pas commencer par : " . TypeDossierEditionService::TYPE_DOSSIER_ID_PASTELL,
                ],
            'RegEx' =>
                [
                    "studio_",
                    "L'identifiant du type de dossier « studio_ » ne respecte pas l'expression rationnelle : " . TypeDossierEditionService::TYPE_DOSSIER_ID_REGEXP,
                ],
            'MaxLength' =>
                [
                    "123456789-123456789-123456789-123",
                    "L'identifiant du type de dossier « 123456789-123456789-123456789-123 » ne respecte pas l'expression rationnelle : " . TypeDossierEditionService::TYPE_DOSSIER_ID_REGEXP,
                ],
        ];
    }

    /**
     * @dataProvider typeDossierIdProvider
     *
     * @param $type_dossier_id
     * @param $exception_message
     */
    public function testCheckTypeDossierId($type_dossier_id, $exception_message)
    {
        $typeDossierProperties = new TypeDossierProperties();
        $typeDossierProperties->id_type_dossier = $type_dossier_id;
        $typeDossierEditionService = $this->getTypeDossierEditionService();

        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage(
            $exception_message
        );
        $typeDossierEditionService->create($typeDossierProperties);
    }

    /**
     * @throws TypeDossierException
     */
    public function testRenameTypeDossierIdFailed()
    {
        $typeDossierProperties = new TypeDossierProperties();
        $typeDossierProperties->id_type_dossier = 'test-42';
        $typeDossierEditionService = $this->getTypeDossierEditionService();
        $id_t = $typeDossierEditionService->create($typeDossierProperties);

        $typeDossierProperties->id_type_dossier = 'test-43';
        $typeDossierEditionService->edit($id_t, $typeDossierProperties);

        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("L'emplacement du type de dossier « test-43 » est déjà utilisé.");

        $this->getTypeDossierEditionService()->renameTypeDossierId("test-42", "test-43");
    }

    public function testEditLibelleInfo()
    {
        $typeDossierProperties = new TypeDossierProperties();
        $typeDossierProperties->id_type_dossier = "arrete-rh";
        $typeDossierEditionService = $this->getObjectInstancier()->getInstance(TypeDossierEditionService::class);
        $id_t = $typeDossierEditionService->create($typeDossierProperties);

        $this->getTypeDossierEditionService()->editLibelleInfo(
            $id_t,
            "arrete-rh",
            "Flux CD 99",
            "Ceci est un flux de test",
            "Information"
        );
        $this->assertEquals(
            [
                'id_type_dossier' => 'arrete-rh',
                'nom' => 'arrete-rh',
                'type' => 'Flux CD 99',
                'description' => 'Ceci est un flux de test',
                'nom_onglet' => 'Information',
                'formulaireElement' =>
                    [],
                'etape' =>
                    [],
                'restriction_pack' => ''
            ],
            $this->getObjectInstancier()->getInstance(TypeDossierManager::class)->getRawData($id_t)
        );
    }
}
