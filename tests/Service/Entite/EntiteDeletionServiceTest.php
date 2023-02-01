<?php

namespace Pastell\Tests\Service\Entite;

use EntiteSQL;
use Pastell\Service\Entite\EntiteDeletionService;
use PastellTestCase;
use UnrecoverableException;

class EntiteDeletionServiceTest extends PastellTestCase
{
    /**
     * @throws UnrecoverableException
     */
    public function testDelete(): void
    {
        $entiteSQL = $this->getObjectInstancier()->getInstance(EntiteSQL::class);
        static::assertTrue($entiteSQL->exists(2));

        $entiteDeletionService = $this->getObjectInstancier()->getInstance(EntiteDeletionService::class);
        $entiteDeletionService->delete(2);
        $journal_message = $this->getJournal()->getAll()[0]['message'];
        $expected_journal_message = file_get_contents(
            __DIR__ . '/../fixtures/entite_delete_service_journal_message.txt'
        );
        static::assertSame(
            $expected_journal_message,
            $journal_message
        );
        static::assertFalse($entiteSQL->exists(2));
        $log_message = $this->getLogRecords()[0]['message'];
        static::assertMatchesRegularExpression(
            "#^Ajout au journal \(id_j=1\): 3 - 2 - 1 - 0 - Supprimé - $expected_journal_message#",
            $log_message
        );
    }
}
