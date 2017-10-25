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
                $this->equalTo("foo foo")
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
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("metadata.txt"),
                $this->stringContains("toto: ".self::DOCUMENT_TITRE)
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
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("metadata.json"),
                $this->equalTo('{"fichier":["foo.txt"],"fichier_simple":["bar.txt"],"toto":"Titre de mon document","prenom":"Eric"}')
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
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("metadata.xml"),
                $this->equalTo(file_get_contents(__DIR__."/fixtures/metadata.xml"))
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
                $this->equalTo("foo foo")
            );
        $this->GED_NG_Connecteur->expects($this->at(3))
            ->method('saveDocument')
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("metadata.xml"),
                $this->equalTo(file_get_contents(__DIR__."/fixtures/metadata-pastell-name.xml"))
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
                $this->equalTo(self::DOCUMENT_TITRE.".zip"),
                $this->stringContains("PK")
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
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("fichier_metadata_Titre de mon document.json"),
                $this->equalTo('{"fichier":["foo.txt"],"fichier_simple":["bar.txt"],"toto":"Titre de mon document","prenom":"Eric"}')
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
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("metadata.json"),
                $this->equalTo('{"fichier":["foo.txt"],"prenom":"Eric"}')
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
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("metadata.xml"),
                $this->equalTo(file_get_contents(__DIR__."/fixtures/metadata-restriction.xml"))
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
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("foo.txt"),
                $this->equalTo("foo foo")
            );

        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendCleaningDirectory(){
        $this->donneesFormulaire->setData('toto','bl/utr/ep\oi');
        $this->GED_NG_Connecteur->expects($this->at(1))
            ->method('saveDocument')
            ->with(
                $this->equalTo('bl-utr-ep-oi'),
                $this->equalTo("foo.txt"),
                $this->equalTo("foo foo")
            );

        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }

    public function testSendCleaningFilename(){
        $this->donneesFormulaire->addFileFromData("fichier","blu/tre\poi.txt","foo foo");
        $this->GED_NG_Connecteur->expects($this->at(1))
            ->method('saveDocument')
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("blu-tre-poi.txt"),
                $this->equalTo("foo foo")
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
            ->with(
                $this->equalTo(self::DOCUMENT_TITRE),
                $this->equalTo("fichier_termine.txt"),
                $this->equalTo("Le transfert est terminé")
            );

        $this->GED_NG_Connecteur->send($this->donneesFormulaire);
    }
}
