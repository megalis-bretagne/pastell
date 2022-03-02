<?php

use PHPUnit\Framework\MockObject\MockObject;

class DepotConnecteurTest extends PastellTestCase
{
    public const DOCUMENT_TITRE = "Titre de mon document";

    private MockObject|DepotConnecteur $DepotConnecteur;
    private DonneesFormulaire $connecteurConfig;
    private DonneesFormulaire $donneesFormulaire;

    /**
     * @throws NotFoundException
     * @throws Exception
     */
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

    private function callBackTestFile(string $filename, string $filepath): callable
    {
        return $this->callBackTestContent(
            self::DOCUMENT_TITRE,
            $filename,
            file_get_contents($filepath) ?: ''
        );
    }

    private function callBackTestContent(string $directory, string $filename, string $content): callable
    {
        return function ($a, $b, $c) use ($directory, $filename, $content) {
            if ($b !== $filename) {
                return;
            }
            $this->assertEquals($directory, $a);
            $this->assertEquals($filename, $b);
            $this->assertEquals(
                $content,
                file_get_contents($c)
            );
        };
    }

    public function testLecture(): void
    {
        $this->DepotConnecteur
            ->method('listDirectory')
            ->willReturn(array("mock"));
        $this->assertEquals('Contenu du répertoire : ["mock"]', $this->DepotConnecteur->testLecture());
    }

    /**
     * @throws UnrecoverableException
     */
    public function testEcriture(): void
    {
        $this->DepotConnecteur->method('makeDirectory')->willReturn(true);
        $this->DepotConnecteur->method('saveDocument')->willReturn(true);
        $this->DepotConnecteur->method('directoryExists')->willReturn(true);
        $this->assertTrue($this->DepotConnecteur->testEcriture());
    }

    /**
     * @throws UnrecoverableException
     */
    public function testEcritureFailed(): void
    {
        $this->DepotConnecteur->method('makeDirectory')->willReturn(true);
        $this->DepotConnecteur->method('saveDocument')->willReturn(true);
        $this->DepotConnecteur->method('directoryExists')->willReturn(false);
        $this->expectException("UnrecoverableException");
        $this->expectExceptionMessage("Le répertoire créé n'a pas été trouvé !");
        $this->assertTrue($this->DepotConnecteur->testEcriture());
    }

    /**
     * @throws UnrecoverableException
     */
    public function testEcritureFichierFailed(): void
    {
        $this->DepotConnecteur->method('makeDirectory')->willReturn(true);
        $this->DepotConnecteur->method('saveDocument')->willReturn(true);
        $this->DepotConnecteur->method('fileExists')->willReturn(false);
        $this->DepotConnecteur->method('directoryExists')->willReturn(true);
        $this->expectException("UnrecoverableException");
        $this->expectExceptionMessage("Le fichier créé n'a pas été trouvé !");
        $this->assertTrue($this->DepotConnecteur->testEcritureFichier());
    }

    /**
     * @throws UnrecoverableException
     */
    public function testEcritureFichier(): void
    {
        $this->DepotConnecteur->method('saveDocument')->willReturn(true);

        $this->DepotConnecteur->method('fileExists')->willReturn(true);
        $this->assertTrue($this->DepotConnecteur->testEcritureFichier());
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSend(): void
    {
        $this->DepotConnecteur->expects($this->once())
            ->method('makeDirectory')
            ->with(self::DOCUMENT_TITRE);

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(function (string $directory_name, string $filename, string $filepath) {
                if ($filename === "foo.txt") {
                    $this->assertEquals("foo foo", file_get_contents($filepath));
                }
            });

        $this->assertSame(
            [],
            $this->DepotConnecteur->send($this->donneesFormulaire)
        );
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendWithMetadataInYAML(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_YAML_FILE
        );

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestFile(
                    "metadata.txt",
                    __DIR__ . "/fixtures/metadata.yml"
                )
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendWithMetadataInJSON(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_JSON_FILE
        );

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestFile(
                    "metadata.json",
                    __DIR__ . "/fixtures/metadata.json"
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendWithMetadataInXML(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_XML_FILE
        );

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestFile(
                    "metadata.xml",
                    __DIR__ . "/fixtures/metadata.xml"
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSaveWithPastellFilename(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_PASTELL_FILE_FILENAME,
            DepotConnecteur::DEPOT_PASTELL_FILE_FILENAME_PASTELL
        );
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_XML_FILE
        );
        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "aaaa.yml_fichier_0",
                    'foo foo'
                )
            );
        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestFile(
                    "metadata.xml",
                    __DIR__ . "/fixtures/metadata-pastell-name.xml"
                )
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSaveZipFile(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_TYPE_DEPOT,
            DepotConnecteur::DEPOT_TYPE_DEPOT_ZIP
        );
        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                function ($a, $b) {
                    $this->assertEquals(self::DOCUMENT_TITRE . ".zip", $b);
                }
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendRepertoireAsExpression(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_TITRE_REPERTOIRE,
            DepotConnecteur::DEPOT_TITRE_REPERTOIRE_METADATA
        );

        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_TITRE_EXPRESSION,
            'expression %toto% avec métadonnée'
        );

        $this->DepotConnecteur
            ->method('makeDirectory')
            ->willReturnCallback(
                function ($a) {
                    $this->assertEquals('expression ' . self::DOCUMENT_TITRE . ' avec métadonnée', $a);
                }
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
            ->method('makeDirectory')
            ->willReturnCallback(
                function ($a) {
                    $this->assertEquals($this->donneesFormulaire->id_d, $a);
                }
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendModifMetadonneFilename(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNES_FILENAME,
            "fichier_metadata_%toto%"
        );

        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_JSON_FILE
        );

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestFile(
                    "fichier_metadata_Titre de mon document.json",
                    __DIR__ . "/fixtures/metadata.json"
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendModifMetadonneRestriction(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES_RESTRICTION,
            "fichier,prenom"
        );

        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_JSON_FILE
        );

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestFile(
                    "metadata.json",
                    __DIR__ . "/fixtures/metadata-restriction.json"
                )
            );


        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendModifMetadonneRestrictionXML(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES_RESTRICTION,
            "fichier,prenom"
        );

        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_METADONNEES,
            DepotConnecteur::DEPOT_METADONNEES_XML_FILE
        );

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestFile(
                    "metadata.xml",
                    __DIR__ . "/fixtures/metadata-restriction.xml"
                )
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendFileRestriction(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_FILE_RESTRICTION,
            "fichier"
        );

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "foo.txt",
                    "foo foo"
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendCleaningDirectory(): void
    {
        $this->donneesFormulaire->setData('toto', 'bl/utr/ep\oi');
        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestContent(
                    'bl-utr-ep-oi',
                    "foo.txt",
                    "foo foo"
                )
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testSendCleaningFilename(): void
    {
        $this->donneesFormulaire->addFileFromData("fichier", "blu/tre\poi.txt", "foo foo");
        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "blu-tre-poi.txt",
                    "foo foo"
                )
            );


        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendFichierTermine(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_CREATION_FICHIER_TERMINE,
            "on"
        );
        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "fichier_termine.txt",
                    "Le transfert est terminé"
                )
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testExceptionIsThrow(): void
    {
        $this->DepotConnecteur
            ->method('saveDocument')
            ->willThrowException(new Exception("foo"));
        $this->expectException('Exception');
        $this->expectExceptionMessage("foo");
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendAlreadyExists(): void
    {
        $this->DepotConnecteur
            ->method('directoryExists')
            ->willReturn(true);
        $this->expectException('UnrecoverableException');
        $this->expectExceptionMessage("Le répertoire Titre de mon document existe déjà !");
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendAlreadyExistsRename(): void
    {
        $this->connecteurConfig->setData(
            DepotConnecteur::DEPOT_EXISTE_DEJA,
            DepotConnecteur::DEPOT_EXISTE_DEJA_RENAME
        );
        $this->DepotConnecteur
            ->method('directoryExists')
            ->willReturn(true);

        $this->DepotConnecteur
            ->method('makeDirectory')
            ->willReturnCallback(
                function ($a) {
                    $this->assertMatchesRegularExpression("#^Titre de mon document_[0-9_]*$#", $a);
                }
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendFilenameAlreadyExists(): void
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

    /**
     * @throws UnrecoverableException
     */
    public function testSendFilenameAlreadyExistsRename(): void
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

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                function ($a, $b) {
                    $this->assertMatchesRegularExpression("#^Titre de mon document_[0-9_]*\.zip$#", $b);
                }
            );
        $this->DepotConnecteur->send($this->donneesFormulaire);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSendWithGedDocumentsId(): void
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

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "foo.txt",
                    "foo foo"
                )
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
    public function testRenameFiles(): void
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

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    'foo-const_0.txt',
                    'foo foo'
                )
            );

        $this->DepotConnecteur
            ->method('saveDocument')
            ->willReturnCallback(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    self::DOCUMENT_TITRE . '-const-bar.txt',
                    'bar bar bar'
                )
            );

        $this->DepotConnecteur->send($this->donneesFormulaire);
    }
}
