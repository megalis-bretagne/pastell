<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierEditionService;
use Pastell\Service\TypeDossier\TypeDossierManager;
use PastellTestCase;
use TypeDossierProperties;

class TypeDossierEditionServiceTest extends PastellTestCase
{
    private function getTypeDossierEditionService()
    {
        return $this->getObjectInstancier()->getInstance(TypeDossierEditionService::class);
    }

    public function testCreateAndEdit()
    {
        $typeDossierProperties = new TypeDossierProperties();
        $typeDossierProperties->id_type_dossier = 'test-42';
        $typeDossierEditionService = $this->getTypeDossierEditionService();
        $id_t = $typeDossierEditionService->create($typeDossierProperties);

        $journal_message = $this->getJournal()->getAll()[0]['message'];
        $journal_export_json = '{' . explode('{', $journal_message, 2)[1];
        $journal_raw_data = json_decode($journal_export_json, true)['raw_data'];
        $expected_journal_raw_data = json_decode(file_get_contents(
            __DIR__ . "/fixtures/type_dossier_create_service_journal_message_raw_data.txt"
        ), true);
        $this->assertSame($expected_journal_raw_data, $journal_raw_data);
        $log_message = $this->getLogRecords()[0]['message'];
        $this->assertRegExp(
            "#Ajout au journal \(id_j=1\): 12 - 0 - 1 - 0 - Ajouté - Ajout du type de dossier id_t=1#",
            $log_message
        );

        $typeDossierProperties->id_type_dossier = 'test-43';
        $typeDossierEditionService->edit($id_t, $typeDossierProperties);

        $journal_message = $this->getJournal()->getAll()[0]['message'];
        $journal_export_json = '{' . explode('{', $journal_message, 2)[1];
        $journal_raw_data = json_decode($journal_export_json, true)['raw_data'];
        $expected_journal_raw_data = json_decode(file_get_contents(
            __DIR__ . "/fixtures/type_dossier_edit_service_journal_message_raw_data.txt"
        ), true);
        $this->assertSame($expected_journal_raw_data, $journal_raw_data);
        $log_message = $this->getLogRecords()[1]['message'];
        $this->assertRegExp(
            "#Ajout au journal \(id_j=2\): 12 - 0 - 1 - 0 - Modifié - Modification du type de dossier id_t=1#",
            $log_message
        );
    }

    public function testEditLibelleInfo()
    {
        $typeDossierProperties = new TypeDossierProperties();
        $typeDossierProperties->id_type_dossier = "test";
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
            array(
                'id_type_dossier' => 'test',
                'nom' => 'arrete-rh',
                'type' => 'Flux CD 99',
                'description' => 'Ceci est un flux de test',
                'nom_onglet' => 'Information',
                'formulaireElement' =>
                    array(),
                'etape' =>
                    array(),
            ),
            $this->getObjectInstancier()->getInstance(TypeDossierManager::class)->getRawData($id_t)
        );
    }
}
