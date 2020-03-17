<?php

class JournalManagerTest extends PastellTestCase
{

    public function testPurge()
    {
        $journalManager = $this->getObjectInstancier()->getInstance(JournalManager::class);
        $this->assertTrue($journalManager->purgeToHistorique());
    }

    /**
     * @throws Exception
     */
    public function testPurgeFailed()
    {
        $id_j = $this->getJournal()->addConsultation(1, "XYZ", 1);
        $this->getSQLQuery()->queryOne("UPDATE journal SET date=? WHERE id_j=?", "1977-02-18", $id_j);

        $this->getSQLQuery()->query("INSERT INTO journal_historique (id_j) VALUES ($id_j) ");

        $journalManager = $this->getObjectInstancier()->getInstance(JournalManager::class);
        $this->assertFalse($journalManager->purgeToHistorique());

        $this->assertEquals(
            "Problème sur la purge du journalSQLSTATE[23000]: " .
            "Integrity constraint violation: 1062 Duplicate entry '$id_j' for key 'PRIMARY' " .
            "- INSERT INTO journal_historique SELECT * FROM journal WHERE id_j=?|$id_j",
            $this->getLogRecords()[3]['message']
        );
    }
}
