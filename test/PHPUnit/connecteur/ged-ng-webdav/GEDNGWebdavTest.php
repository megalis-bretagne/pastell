<?php

require_once( __DIR__.'/../../../../connecteur/ged-ng-webdav/GEDNGWebdav.class.php');


class GEDNGWebdavTest extends PastellTestCase {

    /** @var  GedNGWebdav */
    private $GEDNGWebdav;

    protected function setUp() {
        parent::setUp();
        $webdavWrapper = $this->getMockBuilder('WebdavWrapper')->getMock();
        $webdavWrapper->expects($this->any())->method('listFolder')->willReturn(array('foo'));
        $webdavWrapper->expects($this->any())->method('exists')->willReturn(false);

        $connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $connecteurConfig->setData(GedNGWebdav::GED_WEBDAV_URL,'https://foo/');

        /** @var WebdavWrapper $webdavWrapper*/
        $this->GEDNGWebdav = new GedNGWebdav($webdavWrapper);
        $this->GEDNGWebdav->setConnecteurConfig($connecteurConfig);
    }

    public function testList(){
        $this->assertEquals(
            array('foo'),
            $this->GEDNGWebdav->listDirectory()
        );
    }

    public function testMakeDirectory(){
        $this->assertEquals(
            'bar',
            $this->GEDNGWebdav->makeDirectory('bar')
        );
    }

    public function testSaveDocument(){
        $this->assertEquals(
            'https://foo/foo/bar',
            $this->GEDNGWebdav->saveDocument('foo','bar',__DIR__."/fixtures/toto.txt")
        );
    }

    public function testDirectoryExists(){
        $this->assertFalse(
            $this->GEDNGWebdav->directoryExists('bar')
        );
    }

    public function testFileExists(){
        $this->assertFalse(
            $this->GEDNGWebdav->fileExists('bar')
        );
    }
}