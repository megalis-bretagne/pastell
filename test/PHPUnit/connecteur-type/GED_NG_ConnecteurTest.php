<?php

class GED_NG_ConnecteurTest extends PastellTestCase {

    const DOCUMENT_TITRE = "Titre de mon document";

    /** @var  GED_NG_Connecteur | PHPUnit_Framework_MockObject_MockObject */
    private $GED_NG_Connecteur;

    /** @var  DonneesFormulaire */
    private $connecteurConfig;

    /** @var  DonneesFormulaire */
    private $donneesFormulaire;

    protected function setUp(){
        parent::setUp();

        $this->donneesFormulaire = $this->getDonneesFormulaireFactory()->get('aaaa','test');
        $this->donneesFormulaire->addFileFromData("fichier","foo.txt","foo foo");
        $this->donneesFormulaire->addFileFromData("fichier_simple","bar.txt","bar bar bar");
        $this->donneesFormulaire->setData('toto',self::DOCUMENT_TITRE);
        $this->donneesFormulaire->setData('prenom',"Eric");

        $this->GED_NG_Connecteur = $this->getMockForAbstractClass('GED_NG_Connecteur');
        $this->connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $this->GED_NG_Connecteur->setConnecteurConfig($this->connecteurConfig);
    }

    private function callBackTestFile($directory,$filename,$filepath) {
        return $this->callBackTestContent($directory,$filename,file_get_contents($filepath));
    }

    private function callBackTestContent($directory,$filename,$content) {
        return $this->returnCallback(function($a,$b,$c) use ($directory,$filename,$content){
            $this->assertEquals($directory,$a);
            $this->assertEquals($filename,$b);
            $this->assertEquals(
                $content,file_get_contents($c));
        });
    }

    public function testLecture(){
        $this->GED_NG_Connecteur->expects($this->any())
            ->method('listDirectory')
            ->willReturn("mock");
        $this->assertEquals('Contenu du répertoire : "mock"', $this->GED_NG_Connecteur->testLecture());
    }

    public function testEcriture(){
        $this->GED_NG_Connecteur->expects($this->any())->method('makeDirectory')->willReturn(true);
        $this->GED_NG_Connecteur->expects($this->any())->method('saveDocument')->willReturn(true);
        $this->assertTrue( $this->GED_NG_Connecteur->testEcriture());
    }

    public function testSend(){
        $this->GED_NG_Connecteur->expects($this->once())
            ->method('makeDirectory')
            ->with($this->equalTo(self::DOCUMENT_TITRE));

        $this->GED_NG_Connecteur->expects($this->at(1))
            ->method('saveDocument')
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("foo.txt"),
                $this->callback(function($filepath){
                    return "foo foo" == file_get_contents($filepath);
                })
            );

        $this->assertTrue($this->GED_NG_Connecteur->send($this->donneesFormulaire));
    }

    public function testSendWithMetadataInYAML(){
        $this->connecteurConfig->setData(
            'ged_metadonnees',
            GED_NG_Connecteur::GED_METADONNEES_YAML_FILE
        );

        $this->GED_NG_Connecteur->expects($this->at(3))
            ->method('saveDocument')
            ->will(
                 $this->returnCallback(
                     function ($a,$b,$c){
                         $this->assertEquals(file_get_contents(__DIR__."/fixtures/metadata.yml"),file_get_contents($c));
                     }
                 )
            )
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("metadata.txt")
            );

        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendWithMetadataInJSON(){
        $this->connecteurConfig->setData(
            'ged_metadonnees',
            GED_NG_Connecteur::GED_METADONNEES_JSON_FILE
        );

        $this->GED_NG_Connecteur->expects($this->at(3))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.json",
                    __DIR__."/fixtures/metadata.json"
                )
            );

        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }


    public function testSendWithMetadataInXML(){
        $this->connecteurConfig->setData(
            'ged_metadonnees',
            GED_NG_Connecteur::GED_METADONNEES_XML_FILE
        );

        $this->GED_NG_Connecteur->expects($this->at(3))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.xml",
                    __DIR__."/fixtures/metadata.xml"
                )
            );

        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSaveWithPastellFilename(){
        $this->connecteurConfig->setData(
            GED_NG_Connecteur::GED_PASTELL_FILE_FILENAME,
            GED_NG_Connecteur::GED_PASTELL_FILE_FILENAME_PASTELL
        );
        $this->connecteurConfig->setData(
            'ged_metadonnees',
            GED_NG_Connecteur::GED_METADONNEES_XML_FILE
        );
        $this->GED_NG_Connecteur->expects($this->at(1))
            ->method('saveDocument')
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("aaaa.yml_fichier_0"),
                $this->callback(function($filepath){
                    return 'foo foo' == file_get_contents($filepath);
                })
            );
        $this->GED_NG_Connecteur->expects($this->at(3))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.xml",
                    __DIR__."/fixtures/metadata-pastell-name.xml"
                )
            );
        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSaveZipFile(){
        $this->connecteurConfig->setData(
            GED_NG_Connecteur::GED_TYPE_DEPOT,
            GED_NG_Connecteur::GED_TYPE_DEPOT_ZIP
        );
        $this->GED_NG_Connecteur->expects($this->at(0))
            ->method('saveDocument')
            ->with(
                $this->equalTo(""),
                $this->equalTo(self::DOCUMENT_TITRE.".zip")
            );
        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendRepertoireAsExpression(){
        $this->connecteurConfig->setData(
            GED_NG_Connecteur::GED_TITRE_REPERTOIRE,
            GED_NG_Connecteur::GED_TITRE_REPERTOIRE_METADATA
        );

        $this->connecteurConfig->setData(
            GED_NG_Connecteur::GED_TITRE_EXPRESSION,
            'expression %toto% avec métadonnée'
        );

        $this->GED_NG_Connecteur->expects($this->at(0))
            ->method('makeDirectory')
            ->with(
                $this->equalTo('expression '.self::DOCUMENT_TITRE.' avec métadonnée')
            );

        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendModifMetadonneFilename(){
        $this->connecteurConfig->setData(
            GED_NG_Connecteur::GED_METADONNES_FILENAME,
            "fichier_metadata_%toto%.json"
        );

        $this->connecteurConfig->setData(
            'ged_metadonnees',
            GED_NG_Connecteur::GED_METADONNEES_JSON_FILE
        );

        $this->GED_NG_Connecteur->expects($this->at(3))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "fichier_metadata_Titre de mon document.json",
                    __DIR__."/fixtures/metadata.json"
                )
            );

        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendModifMetadonneRestriction(){
        $this->connecteurConfig->setData(
            GED_NG_Connecteur::GED_METADONNEES_RESTRICTION,
            "fichier,prenom"
        );

        $this->connecteurConfig->setData(
            'ged_metadonnees',
            GED_NG_Connecteur::GED_METADONNEES_JSON_FILE
        );

        $this->GED_NG_Connecteur->expects($this->at(3))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.json",
                    __DIR__."/fixtures/metadata-restriction.json"
                )
            );


        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendModifMetadonneRestrictionXML(){
        $this->connecteurConfig->setData(
            GED_NG_Connecteur::GED_METADONNEES_RESTRICTION,
            "fichier,prenom"
        );

        $this->connecteurConfig->setData(
            'ged_metadonnees',
            GED_NG_Connecteur::GED_METADONNEES_XML_FILE
        );

        $this->GED_NG_Connecteur->expects($this->at(3))
            ->method('saveDocument')
            ->will(
                $this->callBackTestFile(
                    self::DOCUMENT_TITRE,
                    "metadata.xml",
                    __DIR__."/fixtures/metadata-restriction.xml"
                )
            );
        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendFileRestriction(){
        $this->connecteurConfig->setData(
            GED_NG_Connecteur::GED_FILE_RESTRICTION,
            "fichier"
        );

        $this->GED_NG_Connecteur->expects($this->at(1))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "foo.txt",
                    "foo foo"
                )
            );

        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendCleaningDirectory(){
        $this->donneesFormulaire->setData('toto','bl/utr/ep\oi');
        $this->GED_NG_Connecteur->expects($this->at(1))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    'bl-utr-ep-oi',
                    "foo.txt",
                    "foo foo"
                )
            );
        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendCleaningFilename(){
        $this->donneesFormulaire->addFileFromData("fichier","blu/tre\poi.txt","foo foo");
        $this->GED_NG_Connecteur->expects($this->at(1))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "blu-tre-poi.txt",
                    "foo foo"
                )
            );


        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }
    public function testSendFichierTermine(){
        $this->connecteurConfig->setData(
            GED_NG_Connecteur::GED_CREATION_FICHIER_TERMINE,
            "on"
        );
        $this->GED_NG_Connecteur->expects($this->at(3))
            ->method('saveDocument')
            ->will(
                $this->callBackTestContent(
                    self::DOCUMENT_TITRE,
                    "fichier_termine.txt",
                    "Le transfert est terminé"
                )
            );


        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testExceptionIsThrow(){
        $this->GED_NG_Connecteur->expects($this->any())
            ->method('saveDocument')
            ->willThrowException(new Exception("foo"));
        $this->setExpectedException("Exception","foo");
        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

}
