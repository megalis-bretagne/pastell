<?php

require_once( __DIR__.'/../../../../connecteur/depot-sftp/DepotSFTP.class.php');


class DepotSFTPTest extends PastellTestCase {

    /** @var  DepotSFTP */
    private $depotSFTP;

    protected function setUp() {
        parent::setUp();
        $SFTP = $this->getMockBuilder('SFTP')->disableOriginalConstructor()->getMock();
        $SFTP->expects($this->any())->method('listDirectory')->willReturn(array('foo'));


        $SFTPFactory = $this->getMockBuilder('SFTPFactory')->getMock();
        $SFTPFactory->expects($this->any())->method('getInstance')->willReturn($SFTP);

        $connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $connecteurConfig->setData(DepotSFTP::DEPOT_SFTP_DIRECTORY,'/foo/');

        /** @var SFTPFactory $SFTPFactory*/
        $this->depotSFTP = new DepotSFTP($SFTPFactory);
        $this->depotSFTP->setConnecteurConfig($connecteurConfig);
    }

    public function testList(){
        $this->assertEquals(
            array('foo'),
            $this->depotSFTP->listDirectory()
        );
    }

    public function testMakeDirectory(){
        $this->assertEquals(
            '/foo/bar',
            $this->depotSFTP->makeDirectory('bar')
        );
    }

    public function testSaveDocument(){
        $this->assertEquals(
            '/foo/foo/bar',
            $this->depotSFTP->saveDocument('foo','bar',__DIR__."/fixtures/toto.txt")
        );
    }

    public function testDirectoryExists(){
        $this->assertFalse(
            $this->depotSFTP->directoryExists('bar')
        );
    }

    public function testFileExists(){
        $this->assertFalse(
            $this->depotSFTP->fileExists('bar')
        );
    }
}