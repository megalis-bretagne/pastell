<?php

namespace Pastell\Tests\Service\Document;

use DocumentSQL;
use NotFoundException;
use Pastell\Service\Document\DocumentDeletionService;
use PastellTestCase;

class DocumentDeletionServiceTest extends PastellTestCase
{
    /**
     * @throws NotFoundException
     */
    public function testDelete()
    {
        $documentDeletionService = $this->getObjectInstancier()->getInstance(DocumentDeletionService::class);
        $documentSQL = $this->getObjectInstancier()->getInstance(DocumentSQL::class);

        $id_d = $this->createDocument('test')['id_d'];

        $documentDeletionService->delete($id_d);

        DocumentSQL::clearCache();
        $this->assertFalse($documentSQL->getInfo($id_d));

        $journal_message = $this->getJournal()->getAll()[0]['message'];
        $expected_journal_message = "Le document «  » ($id_d) a été supprimé ";
        $this->assertEquals(
            $expected_journal_message,
            $journal_message
        );
    }
}
