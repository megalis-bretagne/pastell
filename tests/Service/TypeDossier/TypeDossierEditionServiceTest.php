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

        // Pour exclure "timestamp":1586440230 on test le début et la fin du message
        $expected_journal_message_start = file_get_contents(
            __DIR__ . "/fixtures/type_dossier_create_service_journal_message_start.txt"
        );
        $this->assertContains(
            $expected_journal_message_start,
            $journal_message
        );
        $expected_journal_message_end = file_get_contents(
            __DIR__ . "/fixtures/type_dossier_create_service_journal_message_end.txt"
        );
        $this->assertContains(
            $expected_journal_message_end,
            $journal_message
        );
        $log_message = $this->getLogRecords()[0]['message'];
        $this->assertRegExp(
            "#Ajout au journal \(id_j=1\): 12 - 0 - 1 - 0 - Ajouté - Ajout du type de dossier id_t=1#",
            $log_message
        );

        $typeDossierProperties->id_type_dossier = 'test-43';
        $typeDossierEditionService->edit($id_t, $typeDossierProperties);

        $journal_message = $this->getJournal()->getAll()[0]['message'];

        // Pour exclure "timestamp":1586440230 on test le début et la fin du message
        $expected_journal_message_start = file_get_contents(
            __DIR__ . "/fixtures/type_dossier_edit_service_journal_message_start.txt"
        );
        $this->assertContains(
            $expected_journal_message_start,
            $journal_message
        );
        $expected_journal_message_end = file_get_contents(
            __DIR__ . "/fixtures/type_dossier_edit_service_journal_message_end.txt"
        );
        $this->assertContains(
            $expected_journal_message_end,
            $journal_message
        );
        $log_message = $this->getLogRecords()[1]['message'];
        $this->assertRegExp(
            "#Ajout au journal \(id_j=2\): 12 - 0 - 1 - 0 - Modifié - Modification du type de dossier id_t=1#",
            $log_message
        );
    }
}
