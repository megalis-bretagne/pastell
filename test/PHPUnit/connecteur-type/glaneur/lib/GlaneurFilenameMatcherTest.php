<?php

class GlaneurFilenameMatcherTest extends \PHPUnit\Framework\TestCase
{
    /** @var GlaneurFilenameMatcher */
    private $glaneurLocalFilenameMatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->glaneurLocalFilenameMatcher = new GlaneurFilenameMatcher();
    }

    public function testMatch()
    {
        $this->assertEquals(
            ['pes_aller' => ['toto.xml']],
            $this->glaneurLocalFilenameMatcher->getFilenameMatching(
                "pes_aller: #.*#",
                [],
                [ 'toto.xml'  ]
            )['file_match']
        );
    }

    public function testEmpty()
    {
        $this->expectExceptionMessage("Impossible de trouver les expressions pour associer les fichiers");
        $this->glaneurLocalFilenameMatcher->getFilenameMatching(
            "",
            [],
            []
        );
    }


    public function testFilelistEmpty()
    {
        $this->expectExceptionMessage("Impossible d'associer les fichiers");
        $this->glaneurLocalFilenameMatcher->getFilenameMatching(
            "pes_aller: #.*#",
            [],
            []
        );
    }

    public function testPregMatchEmpty()
    {
        $this->expectExceptionMessage("Impossible de trouver les expressions pour associer les fichiers");
        $this->glaneurLocalFilenameMatcher->getFilenameMatching(
            "",
            [],
            [ 'toto.xml'  ]
        );
    }

    public function testMultipleFileMatch()
    {
        $this->assertEquals(
            ['pes_aller' => ['toto.xml','foo.yml']],
            $this->glaneurLocalFilenameMatcher->getFilenameMatching(
                "pes_aller: #.*#",
                [],
                [ 'toto.xml','foo.yml' ]
            )['file_match']
        );
    }

    public function testManyFiles()
    {
        $this->assertEquals(
            ['pes_aller' => ['PES_ALR2_1223.xml'],'pes_acquit' => ['ACK_PES_ALR2_1223.xml']],
            $this->glaneurLocalFilenameMatcher->getFilenameMatching(
                "pes_aller: #^PES.*xml$#\npes_acquit: #^ACK_.*$#",
                [],
                [ 'PES_ALR2_1223.xml','ACK_PES_ALR2_1223.xml' ]
            )['file_match']
        );
    }


    public function testManyFilesCardinalite1()
    {
        $this->assertEquals(
            ['pes_aller' => ['PES_ALR2_1223.xml'],'pes_acquit' => ['ACK_PES_ALR2_1223.xml']],
            $this->glaneurLocalFilenameMatcher->getFilenameMatching(
                "pes_aller: #^PES.*xml$#\npes_acquit: #^ACK_.*$#",
                ['pes_aller' => 1],
                [ 'PES_ALR2_1223.xml','PES_ALR2_1224.xml','ACK_PES_ALR2_1223.xml' ]
            )['file_match']
        );
    }

    public function testUsePrecedingFile()
    {
        $this->assertEquals(
            ['pes_aller' => ['PES_ALR2_1223.xml'],'pes_acquit' => ['ACK_PES_ALR2_1223.xml']],
            $this->glaneurLocalFilenameMatcher->getFilenameMatching(
                "pes_aller: #^(PES_.*xml)$#\npes_acquit: #^ACK_\$matches[0][1]$#",
                ['pes_aller' => 1],
                [ 'PES_ALR2_1223.xml','PES_ALR2_1224.xml','ACK_PES_ALR2_1223.xml' ]
            )['file_match']
        );
    }

    public function testTrimOK()
    {
        $this->assertEquals(
            ['pes_aller' => ['PES_ALR2_1223.xml'],'pes_acquit' => ['ACK_PES_ALR2_1223.xml']],
            $this->glaneurLocalFilenameMatcher->getFilenameMatching(
                "    pes_aller   : #^PES.*xml$#     \n      pes_acquit     :     #^ACK_.*$#    ",
                ['pes_aller' => 1],
                [ 'PES_ALR2_1223.xml','PES_ALR2_1224.xml','ACK_PES_ALR2_1223.xml' ]
            )['file_match']
        );
    }

    public function testBizarre()
    {

        $this->assertEquals(
            ['fichier_pes' => ['PESALR2_A.xml'],'fichier_reponse' => ['ACQUIT_PESALR2_A.xml']],
            $this->glaneurLocalFilenameMatcher->getFilenameMatching(
                'fichier_pes: #^(PESALR2.*)$#' . "\n" . 'fichier_reponse:#^ACQUIT_$matches[0][1]$#',
                ['fichier_pes' => 1,'fichier_reponse' => 1],
                [ 'ACQUIT_PESALR2_A.xml','ACQUIT_PESALR2_B.xml','PESALR2_A.xml','PESALR2_B.xml' ]
            )['file_match']
        );
    }
}
