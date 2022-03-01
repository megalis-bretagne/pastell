<?php

namespace Pastell\Tests\Service;

use Pastell\Service\Utilisateur\UtilisateurDeletionService;
use PastellTestCase;
use UtilisateurSQL;

class UtilisateurDeletionServiceTest extends PastellTestCase
{
    public function testDelete()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $this->assertTrue($utilisateurSQL->exists(2));

        $entiteDeletionService = $this->getObjectInstancier()->getInstance(
            UtilisateurDeletionService::class
        );
        $entiteDeletionService->delete(2);
        $journal_message = $this->getJournal()->getAll()[0]['message'];
        $expected_journal_message = "Suppression de l'utilisateur id_u=2";
        $this->assertEquals(
            $expected_journal_message,
            $journal_message
        );
        $this->assertFalse($utilisateurSQL->exists(2));
        $log_message = $this->getLogRecords()[0]['message'];
        $this->assertMatchesRegularExpression(
            "#^Ajout au journal \(id_j=1\): 4 - 0 - 1 - 0 - Supprimé - $expected_journal_message#",
            $log_message
        );
    }
}
