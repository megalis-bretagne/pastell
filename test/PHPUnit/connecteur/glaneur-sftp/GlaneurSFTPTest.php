<?php

require_once __DIR__ . "/../../../../connecteur/glaneur-sftp/GlaneurSFTP.class.php";


class GlaneurSFTPTest extends PastellTestCase
{

    /** @var  TmpFolder */
    private $tmpFolder;
    private $tmp_folder;
    private $directory_send;
    private $directory_error;

    private $last_message;
    private $created_id_d;

    /** @throws Exception */
    protected function setUp()
    {
        parent::setUp();
        $this->tmpFolder = new TmpFolder();
        $this->tmp_folder = $this->tmpFolder->create();
        $this->directory_send = $this->tmpFolder->create();
        $this->directory_error = $this->tmpFolder->create();
    }

    protected function tearDown()
    {
        $this->tmpFolder->delete($this->tmp_folder);
        $this->tmpFolder->delete($this->directory_send);
        $this->tmpFolder->delete($this->directory_error);
    }

    private function getGlaneurSFTP(array $collectivite_properties)
    {
        $glaneurSFTP = $this->getObjectInstancier()->getInstance(GlaneurSFTP::class);
        $glaneurSFTP->setLogger($this->getLogger());
        $glaneurSFTP->setConnecteurInfo(['id_e' => 1]);
        $collectiviteProperties = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $collectiviteProperties->setTabData($collectivite_properties);
        $glaneurSFTP->setConnecteurConfig($collectiviteProperties);
        return $glaneurSFTP;
    }

    /**
     * @param $collectivite_properties
     * @return string
     * @throws Exception */
    private function glanerWithProperties(array $collectivite_properties, SFTPFactory $sftpFactory = null)
    {

        if (! $sftpFactory) {
            $sftpFactory = $this->getSFTPFactory();
        }
        $glaneurSFTP = $this->getGlaneurSFTP($collectivite_properties);
        $glaneurSFTP->setSFTPFactory($sftpFactory);
        $result = $glaneurSFTP->glaner();
        $this->last_message = $glaneurSFTP->getLastMessage();
        $this->created_id_d = $result;
        return $result;
    }


    /** @throws Exception */
    public function testGlanerVrac()
    {
        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();

        $sftp->expects($this->any())
            ->method('listDirectory')
            ->willReturn([".","..","foo.txt"]);

        $sftp->expects($this->any())
            ->method('isDir')
            ->willReturn(false);

        $sftp->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($a, $b) {
                copy($this->tmp_folder . "/foo.txt", $b);
            });

        $sftpFactory = $this->getMockBuilder(SFTPFactory::class)->disableOriginalConstructor()->getMock();

        $sftpFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($sftp);


        /** @var SFTPFactory $sftpFactory */


        mkdir($this->tmp_folder . "/" . "test1");
        copy(__DIR__ . "/fixtures/foo.txt", $this->tmp_folder . "/foo.txt");
        $this->assertNotFalse(
            $this->glanerWithProperties([
                GlaneurConnecteur::TRAITEMENT_ACTIF => '1',
                GlaneurConnecteur::TYPE_DEPOT => GlaneurConnecteur::TYPE_DEPOT_VRAC,
                GlaneurConnecteur::FILE_PREG_MATCH => 'arrete: #.*#',
                GlaneurConnecteur::FLUX_NAME => 'actes-generique',
                GlaneurConnecteur::ACTION_OK => 'send-tdt',
                GlaneurConnecteur::DIRECTORY => $this->tmp_folder,
                GlaneurConnecteur::DIRECTORY_ERROR => $this->directory_error,
            ], $sftpFactory)
        );

        $document = $this->getObjectInstancier()->getInstance(Document::class);
        $id_d = $document->getAllByType('actes-generique')[0]['id_d'];
        $donneesFormulaireFactory = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEquals('foo.txt', $donneesFormulaireFactory->getFileName('arrete'));
        $this->assertEquals('bar', $donneesFormulaireFactory->getFileContent('arrete'));
    }


    /** @throws Exception */
    public function testGlanerFolder()
    {

        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();

        $sftp->expects($this->any())
            ->method('listDirectory')
            ->willReturnCallback(function ($b) {
                if (basename($b) == 'test1') {
                    return ['.','..','foo.txt'];
                } else {
                    return [".","..","test1"];
                }
            });

        $sftp->expects($this->any())
            ->method('isDir')
            ->willReturnCallback(function ($b) {
                return basename($b) == 'test1';
            });


        $sftp->expects($this->any())
            ->method('exists')
            ->willReturnCallback(function ($b) {
                return false;
            });

        $sftp->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($a, $b) {
                copy($a, $b);
            });


        $sftpFactory = $this->getMockBuilder(SFTPFactory::class)->disableOriginalConstructor()->getMock();

        $sftpFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($sftp);


        mkdir($this->tmp_folder . "/" . "test1");
        copy(__DIR__ . "/fixtures/foo.txt", $this->tmp_folder . "/test1/foo.txt");
        $this->assertNotFalse(
            $this->glanerWithProperties([
                GlaneurConnecteur::TRAITEMENT_ACTIF => '1',
                GlaneurConnecteur::TYPE_DEPOT => GlaneurConnecteur::TYPE_DEPOT_FOLDER,
                GlaneurConnecteur::FILE_PREG_MATCH => 'arrete: #.*#',
                GlaneurConnecteur::FLUX_NAME => 'actes-generique',
                GlaneurConnecteur::ACTION_OK => 'send-tdt',
                GlaneurConnecteur::DIRECTORY => $this->tmp_folder,
                GlaneurConnecteur::DIRECTORY_SEND => $this->directory_send,
                GlaneurConnecteur::DIRECTORY_ERROR => $this->directory_error,
            ], $sftpFactory)
        );

        $document = $this->getObjectInstancier()->getInstance(Document::class);
        $id_d = $document->getAllByType('actes-generique')[0]['id_d'];
        $donneesFormulaireFactory = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEquals('foo.txt', $donneesFormulaireFactory->getFileName('arrete'));
        $this->assertEquals('bar', $donneesFormulaireFactory->getFileContent('arrete'));
    }

    /**
     * @throws Exception
     */
    public function testListFile()
    {
        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();

        $sftp->expects($this->any())
            ->method('listDirectory')
            ->willReturnCallback(function ($b) {
                if (basename($b) == 'test1') {
                    return ['.','..','foo.txt'];
                } else {
                    return [".","..","test1"];
                }
            });

        $sftp->expects($this->any())
            ->method('isDir')
            ->willReturnCallback(function ($b) {
                return basename($b) == 'test1';
            });


        $sftp->expects($this->any())
            ->method('exists')
            ->willReturnCallback(function ($b) {
                return false;
            });

        $sftp->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($a, $b) {
                copy($a, $b);
            });


        $sftpFactory = $this->getMockBuilder(SFTPFactory::class)->disableOriginalConstructor()->getMock();

        $sftpFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($sftp);

        $glaneurSFTP = $this->getGlaneurSFTP([
            GlaneurConnecteur::TRAITEMENT_ACTIF => '1',
            GlaneurConnecteur::TYPE_DEPOT => GlaneurConnecteur::TYPE_DEPOT_FOLDER,
            GlaneurConnecteur::FILE_PREG_MATCH => 'arrete: #.*#',
            GlaneurConnecteur::FLUX_NAME => 'actes-generique',
            GlaneurConnecteur::ACTION_OK => 'send-tdt',
            GlaneurConnecteur::DIRECTORY => $this->tmp_folder,
            GlaneurConnecteur::DIRECTORY_SEND => $this->directory_send,
            GlaneurConnecteur::DIRECTORY_ERROR => $this->directory_error,
        ]);

        $glaneurSFTP->setSFTPFactory($sftpFactory);
        $this->assertRegExp("#test1#", $glaneurSFTP->listDirectories());
    }

    /**
     * @return SFTPFactory
     */
    private function getSFTPFactory()
    {
        $sftp = $this->getMockBuilder(SFTP::class)->disableOriginalConstructor()->getMock();

        $sftp->expects($this->any())
            ->method('listDirectory')
            ->willReturnCallback(function ($b) {
                if (basename($b) == 'test1') {
                    return ['.','..','foo.txt'];
                } else {
                    return [".","..","test1"];
                }
            });

        $sftp->expects($this->any())
            ->method('isDir')
            ->willReturnCallback(function ($b) {
                return basename($b) == 'test1';
            });


        $sftp->expects($this->any())
            ->method('exists')
            ->willReturn(false);

        $sftp->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($a, $b) {
                copy($a, $b);
            });


        $sftpFactory = $this->getMockBuilder(SFTPFactory::class)->disableOriginalConstructor()->getMock();

        $sftpFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($sftp);
        /** @var SFTPFactory $sftpFactory */
        return $sftpFactory;
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testGlanerFicExample()
    {
        $glaneurSFTP = $this->getObjectInstancier()->getInstance(GlaneurSFTP::class);
        $glaneurSFTP->setLogger($this->getLogger());
        $glaneurSFTP->setConnecteurInfo(['id_e' => 1]);
        $collectiviteProperties = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $collectiviteProperties->setTabData([
            GlaneurConnecteur::TRAITEMENT_ACTIF => '1',
            GlaneurConnecteur::TYPE_DEPOT => GlaneurConnecteur::TYPE_DEPOT_ZIP,
            GlaneurConnecteur::FILE_PREG_MATCH => 'fichier_reponse: /^(.*)_ack.xml$/' . "\n" . 'fichier_pes: /^$matches[0][1].xml$/',
            GlaneurConnecteur::METADATA_STATIC =>
                "objet: %fichier_pes%\n
                envoi_sae: true\n
                has_reponse: true",
            GlaneurConnecteur::FLUX_NAME => 'helios-automatique',
            GlaneurConnecteur::ACTION_OK => 'importation',
            GlaneurConnecteur::DIRECTORY => $this->tmp_folder,
            GlaneurConnecteur::DIRECTORY_SEND => $this->directory_send,
            GlaneurConnecteur::DIRECTORY_ERROR => $this->directory_error,
        ]);
        $collectiviteProperties->addFileFromCopy(
            GlaneurConnecteur::FICHER_EXEMPLE,
            'pes.zip',
            __DIR__ . '/fixtures/HELIOS_SIMU_ALR2_1547544424_844200543.zip'
        );
        $glaneurSFTP->setConnecteurConfig($collectiviteProperties);

        $glaneurSFTP->setSFTPFactory($this->getSFTPFactory());

        $id_d = $glaneurSFTP->glanerFicExemple();
        $this->assertSame("Création du document $id_d", $glaneurSFTP->getLastMessage()[0]);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $this->assertSame(
            'HELIOS_SIMU_ALR2_1547544424_844200543.xml',
            $donneesFormulaire->getFileName('fichier_pes')
        );
        $this->assertSame(
            'HELIOS_SIMU_ALR2_1547544424_844200543_ack.xml',
            $donneesFormulaire->getFileName('fichier_reponse')
        );
    }
}
