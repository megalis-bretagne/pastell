<?php

namespace Pastell\Tests\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierDeletionService;
use PastellTestCase;
use TypeDossierException;
use TypeDossierSQL;

class TypeDossierDeletionServiceTest extends PastellTestCase
{
    /**
     * @throws TypeDossierException
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
        $this->assertFalse($typeDossierSQL->exists($id_t));

        $journal_message = $this->getJournal()->getAll()[0]['message'];
        $journal_export_json = '{' . explode('{', $journal_message, 2)[1];
        $journal_raw_data = json_decode($journal_export_json, true)['raw_data'];
        $expected_journal_raw_data = json_decode(file_get_contents(
            __DIR__ . "/fixtures/type_dossier_delete_service_journal_message_raw_data.txt"
        ), true);
        $this->assertEquals(
            $expected_journal_raw_data,
            $journal_raw_data
        );
        $log_message = $this->getLogRecords()[1]['message'];
        $this->assertRegExp(
            "#Ajout au journal \(id_j=2\): 12 - 0 - 1 - 0 - Supprimé - Suppression du type de dossier id_t=1#",
            $log_message
        );
    }
}
