<?php

require_once( __DIR__.'/../../../../connecteur/ged-local/GEDLocal.class.php');

class GEDLocalTest extends PastellTestCase {

    /** @var TmpFolder */
    private $tmpFolder;
    private $tmp_folder;

    /** @var DonneesFormulaire */
    private $connecteurConfig;

    /** @var DepotLocal */
    private $gedLocal;

    protected function setUp() {
        parent::setUp();
        $this->tmpFolder = new TmpFolder();
        $this->tmp_folder = $this->tmpFolder->create();
        $this->gedLocal =  new DepotLocal();
        $this->connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $this->connecteurConfig->setData('ged_local_directory',$this->tmp_folder);
        $this->gedLocal->setConnecteurConfig($this->connecteurConfig);
        mkdir("{$this->tmp_folder}/foo/");
    }

    protected function tearDown() {
        $this->tmpFolder->delete($this->tmp_folder);
        parent::tearDown();
    }

    public function testListDirectory(){
        $this->assertEquals(
            array('.','..','foo'),
            $this->gedLocal->listDirectory()
        );
    }

    public function testError(){
        $this->connecteurConfig->setData('ged_local_directory','directory_not_existing');
        $this->setExpectedException(
            "Exception",
            "Erreur lors de l'accès au répertoire : scandir(): (errno 2): No such file or directory"
        );
        $this->gedLocal->listDirectory();
    }

    public function testMakeDirectory(){
        $this->gedLocal->makeDirectory("toto");
        $this->assertEquals(
            array('.','..','foo','toto'),
            $this->gedLocal->listDirectory()
        );
    }

    public function testSaveDocument(){
        $this->gedLocal->saveDocument("","toto.txt",__DIR__."/fixtures/toto.txt");
        $this->assertEquals("toto",file_get_contents($this->tmp_folder."/toto.txt"));
    }

    public function testIsDirectory(){
        $this->assertTrue($this->gedLocal->directoryExists('foo'));
        $this->assertFalse($this->gedLocal->directoryExists('foo2'));
    }

    public function testIsFile(){
        $this->assertFalse($this->gedLocal->fileExists('fichier_inexistant'));
    }
}