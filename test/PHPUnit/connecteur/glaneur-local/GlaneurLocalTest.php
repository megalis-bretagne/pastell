<?php

require_once __DIR__."/../../../../connecteur/glaneur-local/GlaneurLocal.php";
class GlaneurLocalTest extends PastellTestCase {

    /** @var  TmpFolder */
    private $tmpFolder;
    private $tmp_folder;

    private $last_message;

    protected function setUp() {
        parent::setUp();
        $this->tmpFolder = new TmpFolder();
        $this->tmp_folder = $this->tmpFolder->create();
    }

    protected function tearDown() {
        $this->tmpFolder->delete($this->tmp_folder);
    }

    private function glanerWithProperties(array $collectvite_properties){
        $glaneurLocal = new GlaneurLocal();
        $collectiviteProperties = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $collectiviteProperties->setTabData($collectvite_properties);
        $glaneurLocal->setConnecteurConfig($collectiviteProperties);
        $result =  $glaneurLocal->glaner();
        $this->last_message = $glaneurLocal->getLastMessage();
        return $result;
    }

    public function testGlanerDirectoryEmpty(){
        $this->expectExceptionMessage("Directory name must not be empty.");
        $this->glanerWithProperties([
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER
        ]);
    }

    public function testGlanerDirectoryNotFound(){
        $this->expectExceptionMessage("DirectoryIterator::__construct(foo): failed to open dir: No such file or directory");
        $this->glanerWithProperties([
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => 'foo'
        ]);
    }

    public function testGlanerEmptyDirectory(){
        $this->assertTrue($this->glanerWithProperties([
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => $this->tmp_folder
        ]));
        $this->assertEquals("Le répertoire est vide",$this->last_message);
    }

    public function testGlanerFolderFile(){
        mkdir($this->tmp_folder."/"."test1");
        copy(__DIR__."/fixtures/foo.txt",$this->tmp_folder."/"."test1/foo.txt");
        $this->assertTrue($this->glanerWithProperties([
            GlaneurLocal::TYPE_DEPOT => GlaneurLocal::TYPE_DEPOT_FOLDER,
            GlaneurLocal::DIRECTORY => $this->tmp_folder
        ]));


    }


}