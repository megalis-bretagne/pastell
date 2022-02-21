<?php

class DepotConnecteurTest extends PastellTestCase
{
    public const DOCUMENT_TITRE = "Titre de mon document";

    /** @var  DepotConnecteur|PHPUnit_Framework_MockObject_MockObject */
    private $DepotConnecteur;

    /** @var  DonneesFormulaire */
    private $connecteurConfig;

    /** @var  DonneesFormulaire */
    private $donneesFormulaire;

    protected function setUp(): void
    {
        parent::setUp();

        $this->donneesFormulaire = $this->getDonneesFormulaireFactory()->get('aaaa', 'test');
        $this->donneesFormulaire->addFileFromData("fichier", "foo.txt", "foo foo");
        $this->donneesFormulaire->addFileFromData("fichier_simple", "bar.txt", "bar bar bar");
        $this->donneesFormulaire->setData('toto', self::DOCUMENT_TITRE);
        $this->donneesFormulaire->setData('prenom', "Eric");

        $this->DepotConnecteur = $this->getMockForAbstractClass(DepotConnecteur::class);
        $this->connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $this->DepotConnecteur->setConnecteurConfig($this->connecteurConfig);
    }

    private function callBackTestFile($directory, $filename, $filepath)
    {
        return $this->callBackTestContent($directory, $filename, file_get_contents($filepath));
    }

    private function callBackTestContent($directory, $filename, $content)
    {
        return $this->returnCallback(function ($a, $b, $c) use ($directory, $filename, $content) {
            $this->assertEquals($directory, $a);
            $this->assertEquals($filename, $b);
            $this->assertEquals(
                $content,
                file_get_contents($c)
            );
        });
    }

    public function testLecture()
    {
        $this->DepotConnecteur
            ->method('listDirectory')
            ->willReturn(array("mock"));
        $this->assertEquals('Contenu du répertoire : ["mock"]', $this->DepotConnecteur->testLecture());
    }

    public function testEcriture()
    {
        $this->DepotConnecteur->method('makeDirectory')->willReturn(true);
        $this->DepotConnecteur->method('saveDocument')->willReturn(true);
        $this->DepotConnecteur->method('directoryExists')->willReturn(true);
        $this->assertTrue($this->DepotConnecteur->testEcriture());
    }

    public function testEcritureFailed()
    {
        $this->DepotConnecteur->method('makeDirectory')->willReturn(true);
        $this->DepotConnecteur->method('saveDocument')->willReturn(true);
        $this->DepotConnecteur->method('directoryExists')->willReturn(false);
        $this->expectException("UnrecoverableException");
        $this->expectExceptionMessage("Le répertoire créé n'a pas été trouvé !");
        $this->assertTrue($this->DepotConnecteur->testEcriture());
    }

    public function testEcritureFichierFailed()
    {
        $this->DepotConnecteur->method('makeDirectory')->willReturn(true);
        $this->DepotConnecteur->method('saveDocument')->willReturn(true);
        $this->DepotConnecteur->method('fileExists')->willReturn(false);
        $this->DepotConnecteur->method('directoryExists')->willReturn(true);
        $this->expectException("UnrecoverableException");
        $this->expectExceptionMessage("Le fichier créé n'a pas été trouvé !");
        $this->assertTrue($this->DepotConnecteur->testEcritureFichier());
    }

    public function testEcritureFichier()
    {
        $this->DepotConnecteur->method('saveDocument')->willReturn(true);

        $this->DepotConnecteur->method('fileExists')->willReturn(true);
        $this->assertTrue($this->DepotConnecteur->testEcritureFichier());
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSend()
    {
        $this->DepotConnecteur->expects($this->once())
            ->method('makeDirectory')
            ->with(self::DOCUMENT_TITRE);

        $this->DepotConnecteur->expects($this->at(2))
            ->method('saveDocument')
            ->with(
                self::DOCUMENT_TITRE,
                "foo.txt",
                $this->callback(function ($filepath) {
                    return "foo foo" == file_get_contents($filepath);
                })
            );

        $this->assertSame(
            [],
            $this->DepotConnecteur->send($this->donneesFormulaire)
        );
    }

    public function testSendWithMetadataInYAML()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_YAML_FILE
        );

        $this->DepotConnecteur->expects($this->at(4))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.txt",
                    __DIR__ . "/fixtures/metadata.yml"
                )
            );


        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendWithMetadataInJSON()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_JSON_FILE
        );

        $this->DepotConnecteur->expects($this->at(4))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.json",
                    __DIR__ . "/fixtures/metadata.json"
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendWithMetadataInXML()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_XML_FILE
        );

        $this->DepotConnecteur->expects($this->at(4))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.xml",
                    __DIR__ . "/fixtures/metadata.xml"
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSaveWithPastellFilename()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_PASTELL_FILE_FILENAME,
            DepotConnecteur::DEPOT_PASTELL_FILE_FILENAME_PASTELL
        );
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_XML_FILE
        );
        $this->DepotConnecteur->expects($this->at(2))
            ->method('saveDocument')
            ->with(
                self::DOCUMENT_TITRE,
                "aaaa.yml_fichier_0",
                $this->callback(function ($filepath) {
                    return 'foo foo' == file_get_contents($filepath);
                })
            );
        $this->DepotConnecteur->expects($this->at(4))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.xml",
                    __DIR__ . "/fixtures/metadata-pastell-name.xml"
                )
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSaveZipFile()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_TYPE_DEPOT,
            DepotConnecteur::DEPOT_TYPE_DEPOT_ZIP
        );
        $this->DepotConnecteur->expects($this->at(1))
            ->method('saveDocument')
            ->with(
                "",
                self::DOCUMENT_TITRE . ".zip"
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendRepertoireAsExpression()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_TITRE_REPERTOIRE,
            DepotConnecteur::DEPOT_TITRE_REPERTOIRE_METADATA
        );

        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_TITRE_EXPRESSION,
            'expression %toto% avec métadonnée'
        );

        $this->DepotConnecteur->expects($this->at(1))
            ->method('makeDirectory')
            ->with(
                'expression ' . self::DOCUMENT_TITRE . ' avec métadonnée'
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendDirectoryAsDocumentId(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_TITRE_REPERTOIRE,
            DepotConnecteur::DEPOT_TITRE_REPERTOIRE_ID_DOCUMENT
        );

        $this->DepotConnecteur
            ->expects($this->at(1))
            ->method('makeDirectory')
            ->with($this->donneesFormulaire->id_d);

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendModifMetadonneFilename()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNES_FILENAME,
            "fichier_metadata_%toto%"
        );

        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_JSON_FILE
        );

        $this->DepotConnecteur->expects($this->at(4))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "fichier_metadata_Titre de mon document.json",
                    __DIR__ . "/fixtures/metadata.json"
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendModifMetadonneRestriction()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES_RESTRICTION,
            "fichier,prenom"
        );

        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_JSON_FILE
        );

        $this->DepotConnecteur->expects($this->at(4))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.json",
                    __DIR__ . "/fixtures/metadata-restriction.json"
                )
            );


        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendModifMetadonneRestrictionXML()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES_RESTRICTION,
            "fichier,prenom"
        );

        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_XML_FILE
        );

        $this->DepotConnecteur->expects($this->at(4))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.xml",
                    __DIR__ . "/fixtures/metadata-restriction.xml"
                )
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendFileRestriction()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_FILE_RESTRICTION,
            "fichier"
        );

        $this->DepotConnecteur->expects($this->at(2))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "foo.txt",
                    "foo foo"
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendCleaningDirectory()
    {
        $this->donneesFormulaire->setData('toto', 'bl/utr/ep\oi');
        $this->DepotConnecteur->expects($this->at(2))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    'bl-utr-ep-oi',
                    "foo.txt",
                    "foo foo"
                )
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendCleaningFilename()
    {
        $this->donneesFormulaire->addFileFromData("fichier", "blu/tre\poi.txt", "foo foo");
        $this->DepotConnecteur->expects($this->at(2))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "blu-tre-poi.txt",
                    "foo foo"
                )
            );


        $this->DepotConnecteur->send($this->donneesFormulaire);
    }
    public function testSendFichierTermine()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_CREATION_FICHIER_TERMINE,
            "on"
        );
        $this->DepotConnecteur->expects($this->at(4))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "fichier_termine.txt",
                    "Le transfert est terminé"
                )
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testExceptionIsThrow()
    {
        $this->DepotConnecteur
            ->method('saveDocument')
            ->willThrowException(new Exception("foo"));
        $this->expectException('Exception');
        $this->expectExceptionMessage("foo");
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendAlreadyExists()
    {
        $this->DepotConnecteur
            ->method('directoryExists')
            ->willReturn(true);
        $this->expectException('UnrecoverableException');
        $this->expectExceptionMessage("Le répertoire Titre de mon document existe déjà !");
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendAlreadyExistsRename()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_EXISTE_DEJA,
            DepotConnecteur::DEPOT_EXISTE_DEJA_RENAME
        );
        $this->DepotConnecteur
            ->method('directoryExists')
            ->willReturn(true);

        $this->DepotConnecteur->expects($this->at(1))
            ->method('makeDirectory')
            ->with(
                $this->matchesRegularExpression("#^Titre de mon document_[0-9_]*$#")
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendFilenameAlreadyExists()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_TYPE_DEPOT,
            DepotConnecteur::DEPOT_TYPE_DEPOT_ZIP
        );
        $this->DepotConnecteur
            ->method('fileExists')
            ->willReturn(true);
        $this->expectException('UnrecoverableException');
        $this->expectExceptionMessage("Le fichier Titre de mon document.zip existe déjà !");
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    public function testSendFilenameAlreadyExistsRename()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_EXISTE_DEJA,
            DepotConnecteur::DEPOT_EXISTE_DEJA_RENAME
        );
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_TYPE_DEPOT,
            DepotConnecteur::DEPOT_TYPE_DEPOT_ZIP
        );
        $this->DepotConnecteur
            ->method('fileExists')
            ->willReturn(true);

        $this->DepotConnecteur->expects($this->at(1))
            ->method('saveDocument')
            ->with(
                $this->anything(),
                $this->matchesRegularExpression("#^Titre de mon document_[0-9_]*\.zip$#")
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendWithGedDocumentsId()
    {
        $this->DepotConnecteur = $this->getMockForAbstractClass(
            DepotConnecteur::class,
            [],
            '',
            true,
            true,
            true,
            ['getGedDocumentsId']
        );

        $this->connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $this->DepotConnecteur->setConnecteurConfig($this->connecteurConfig);

        $this->DepotConnecteur->expects($this->once())
            ->method('makeDirectory')
            ->with(self::DOCUMENT_TITRE);

        $this->DepotConnecteur->expects($this->at(2))
            ->method('saveDocument')
            ->with(
                self::DOCUMENT_TITRE,
                "foo.txt",
                $this->callback(function ($filepath) {
                    return "foo foo" == file_get_contents($filepath);
                })
            );

        $this->DepotConnecteur
            ->method('getGedDocumentsId')
            ->willReturn([
                'vide.pdf' => '13c631ec-497d-423a-a866-12447ae9708f;1.0',
                'file1.pdf' => '3fa52501-d610-455e-a6cc-3e5cd28da7a4;1.0'
            ]);

        $this->assertSame(
            [
                'vide.pdf' => '13c631ec-497d-423a-a866-12447ae9708f;1.0',
                'file1.pdf' => '3fa52501-d610-455e-a6cc-3e5cd28da7a4;1.0'
            ],
            $this->DepotConnecteur->send($this->donneesFormulaire)
        );
    }

    /**
     * @throws UnrecoverableException
     */
    public function testRenameFiles()
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_PASTELL_FILE_FILENAME,
            DepotConnecteur::DEPOT_PASTELL_FILE_FILENAME_REGEX
        );
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_FILENAME_PREG_MATCH,
            "
            fichier: %fichier%-const\n
            fichier_simple: %toto%-const-%fichier_simple%
            "
        );

        $this->DepotConnecteur->expects($this->at(2))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    'foo-const_0.txt',
                    'foo foo'
                )
            );

        $this->DepotConnecteur->expects($this->at(3))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    self::DOCUMENT_TITRE . '-const-bar.txt',
                    'bar bar bar'
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }
}
