<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierDeletionService;
use PastellTestCase;
use TypeDossierException;
use TypeDossierSQL;
use UnrecoverableException;

class TypeDossierDeletionServiceTest extends PastellTestCase
{
    /**
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    public function testDelete()
    {
        $typeDossierSQL = $this->getObjectInstancier()->getInstance(TypeDossierSQL::class);
        $id_t = $this->copyTypeDossierTest();
        $this->assertTrue($typeDossierSQL->exists($id_t));
        $typeDossierDeletionService = $this->getObjectInstancier()->getInstance(
            TypeDossierDeletionService::class
        );
        $typeDossierDeletionService->delete($id_t);
        $journal_message = $this->getJournal()->getAll()[0]['message'];

        // Pour exclure "timestamp":1586440230 on test le début et la fin du message
        $expected_journal_message_start = file_get_contents(
            __DIR__ . "/fixtures/type_dossier_delete_service_journal_message_start.txt"
        );
        $this->assertContains(
            $expected_journal_message_start,
            $journal_message
        );
        $expected_journal_message_end = file_get_contents(
            __DIR__ . "/fixtures/type_dossier_delete_service_journal_message_end.txt"
        );
        $this->assertContains(
            $expected_journal_message_end,
            $journal_message
        );
        $this->assertFalse($typeDossierSQL->exists($id_t));
        $log_message = $this->getLogRecords()[1]['message'];
        $this->assertRegExp(
            "#Ajout au journal \(id_j=2\): 12 - 0 - 1 - 0 - Supprimé - Suppression du type de dossier id_t=1#",
            $log_message
        );
    }
}
