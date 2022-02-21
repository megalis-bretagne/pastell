<?php

class DepotLocalTest extends PastellTestCase
{
    /** @var TmpFolder */
    private $tmpFolder;
    private $tmp_folder;

    /** @var DonneesFormulaire */
    private $connecteurConfig;

    /** @var DepotLocal */
    private $depotLocal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpFolder = new TmpFolder();
        $this->tmp_folder = $this->tmpFolder->create();
        $this->depotLocal =  new DepotLocal();
        $this->connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $this->connecteurConfig->setData(DepotLocal::DEPOT_LOCAL_DIRECTORY, $this->tmp_folder);
        $this->depotLocal->setConnecteurConfig($this->connecteurConfig);
        mkdir("{$this->tmp_folder}/foo/");
    }

    protected function tearDown(): void
    {
        $this->tmpFolder->delete($this->tmp_folder);
        parent::tearDown();
    }

    public function testListDirectory()
    {
        $this->assertEquals(
            array('.','..','foo'),
            $this->depotLocal->listDirectory()
        );
    }

    public function testError()
    {
        $this->connecteurConfig->setData(
            DepotLocal::DEPOT_LOCAL_DIRECTORY,
            'directory_not_existing'
        );
        $this->expectException("Exception");
        $this->expectExceptionMessage(
            "Erreur lors de l'accès au répertoire : scandir(): (errno 2): No such file or directory"
        );
        $this->depotLocal->listDirectory();
    }

    public function testMakeDirectory()
    {
        $this->depotLocal->makeDirectory("toto");
        $this->assertEquals(
            array('.','..','foo','toto'),
            $this->depotLocal->listDirectory()
        );
    }

    public function testSaveDocument()
    {
        $this->depotLocal->saveDocument("", "toto.txt", __DIR__ . "/fixtures/toto.txt");
        $this->assertEquals("toto", file_get_contents($this->tmp_folder . "/toto.txt"));
    }

    public function testIsDirectory()
    {
        $this->assertTrue($this->depotLocal->directoryExists('foo'));
        $this->assertFalse($this->depotLocal->directoryExists('foo2'));
    }

    public function testIsFile()
    {
        $this->assertFalse($this->depotLocal->fileExists('fichier_inexistant'));
    }
}
