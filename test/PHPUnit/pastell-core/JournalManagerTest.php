<?php

class JournalManagerTest extends PastellTestCase
{
    public function testPurge()
    {
        $journalManager = $this->getObjectInstancier()->getInstance(JournalManager::class);
        $this->assertTrue($journalManager->purgeToHistorique());
    }
}
