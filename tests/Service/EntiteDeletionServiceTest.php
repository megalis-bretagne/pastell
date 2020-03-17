<?php

namespace Pastell\Tests\Service;

use EntiteSQL;
use Exception;
use Pastell\Service\EntiteDeletionService;
use PastellTestCase;

class EntiteDeletionServiceTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testDelete()
    {
        $entiteSQL = $this->getObjectInstancier()->getInstance(EntiteSQL::class);
        $this->assertTrue($entiteSQL->exists(2));

        $entiteDeletionService = $this->getObjectInstancier()->getInstance(EntiteDeletionService::class);
        $entiteDeletionService->delete(2);
        $journal_message = $this->getJournal()->getAll()[0]['message'];
        $expected_journal_message = file_get_contents(
            __DIR__ . "/fixtures/entite_delete_service_journal_message.txt"
        );
        $this->assertEquals(
            $expected_journal_message,
            $journal_message
        );
        $this->assertFalse($entiteSQL->exists(2));
        $log_message = $this->getLogRecords()[0]['message'];
        $this->assertRegExp(
            "#^Ajout au journal \(id_j=1\): 3 - 2 - 1 - 0 - Supprimé - $expected_journal_message#",
            $log_message
        );
    }
}
