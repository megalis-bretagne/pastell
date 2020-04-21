<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierEditionService;
use PastellTestCase;
use TypeDossierProperties;

class TypeDossierEditionServiceTest extends PastellTestCase
{
    public function testEdit()
    {
        $typeDossierProperties = new TypeDossierProperties();
        $typeDossierProperties->id_type_dossier = 'test-42';
        $typeDossierEditionService = $this->getObjectInstancier()->getInstance(TypeDossierEditionService::class);
        $id_t = $typeDossierEditionService->edit(0, $typeDossierProperties);

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
}
