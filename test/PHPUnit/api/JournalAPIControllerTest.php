<?php

class JournalAPIControllerTest extends PastellTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->getJournal()->add(Journal::TEST, 0, '', 'test', "Test");
    }

    public function testList()
    {
        $info = $this->getInternalAPI()->get("/journal");
        $this->assertEquals('Test', $info[0]['message']);
    }

    public function testCSV()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Exit called with code 0");
        $this->expectOutputRegex("#Test#");
        $this->getInternalAPI()->get("/journal?format=csv&csv_entete_colonne=1");
    }

    public function testV1()
    {
        $this->expectOutputRegex("#Test#");
        $this->getV1("journal.php");
    }

    public function testDetail()
    {
        $info = $this->getInternalAPI()->get("/journal/1");
        $this->assertEquals('Test', $info['message']);
    }

    public function testDetailFailed()
    {
        $this->expectException("NotFoundException");
        $this->expectExceptionMessage("L'événement 42 n'a pas été trouvé");
        $this->getInternalAPI()->get("/journal/42");
    }

    public function testJeton()
    {
        $this->expectException("Exception");
        $this->expectExceptionMessage("Exit called with code 0");
        $this->expectOutputRegex("#pastell-journal-preuve-1.tsr#");
        $this->getInternalAPI()->get("/journal/1/jeton");
    }

    public function testPreuveFailed()
    {
        $this->expectException("NotFoundException");
        $this->expectExceptionMessage("Ressource foo non trouvée");
        $this->getInternalAPI()->get("/journal/1/foo");
    }
}
