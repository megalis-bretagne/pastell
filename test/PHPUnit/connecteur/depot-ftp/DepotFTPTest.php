<?php

require_once(__DIR__ . '/../../../../connecteur/depot-ftp/DepotFTP.class.php');


class DepotFTPTest extends PastellTestCase
{
    /** @var  DepotFTP */
    private $depotFTP;

    protected function setUp()
    {
        parent::setUp();
        $FTPClientWrapper = $this->createMock('FtpClientWrapper');
        $FTPClientWrapper->method('login')->willReturn(true);
        $FTPClientWrapper->method('mkdir')->willReturn(true);
        $FTPClientWrapper->method('nlist')->willReturn(array('foo'));

        $connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $connecteurConfig->setData(DepotFTP::DEPOT_FTP_DIRECTORY, '/foo/');

        /** @var FTPClientWrapper $FTPClientWrapper*/
        $this->depotFTP = new DepotFTP();
        $this->depotFTP->setFtpClient($FTPClientWrapper);
        $this->depotFTP->setConnecteurConfig($connecteurConfig);
    }

    public function testList()
    {
        $this->assertEquals(
            array('foo'),
            $this->depotFTP->listDirectory()
        );
    }

    public function testMakeDirectory()
    {
        $this->assertEquals(
            '/foo/bar',
            $this->depotFTP->makeDirectory('bar')
        );
    }

    public function testSaveDocument()
    {
        $this->assertEquals(
            '/foo/foo/bar',
            $this->depotFTP->saveDocument('foo', 'bar', __DIR__ . "/fixtures/toto.txt")
        );
    }

    public function testDirectoryExists()
    {
        $this->assertFalse(
            $this->depotFTP->directoryExists('bar')
        );
    }

    public function testFileExists()
    {
        $this->assertFalse(
            $this->depotFTP->fileExists('bar')
        );
    }
}
