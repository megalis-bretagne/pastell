<?php

require_once __DIR__ . "/../../pastell-core/type-dossier/TypeDossierLoader.class.php";

class TypeDossierSAETest extends PastellTestCase
{

    public const SAE_ONLY = 'sae-only';

    /** @var TypeDossierLoader */
    private $typeDossierLoader;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();
        $this->typeDossierLoader = $this->getObjectInstancier()->getInstance(TypeDossierLoader::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->typeDossierLoader->unload();
    }

    /**
     * @throws Exception
     */
    public function testEtapeSAE()
    {
        $this->typeDossierLoader->createTypeDossierDefinitionFile(self::SAE_ONLY);

        $info_connecteur = $this->createConnector(SedaNG::CONNECTEUR_ID, "Bordereau SEDA");
        $this->associateFluxWithConnector($info_connecteur['id_ce'], self::SAE_ONLY, "Bordereau SEDA");

        $connecteurInfo = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($info_connecteur['id_ce']);

        $connecteurInfo->addFileFromCopy('schema_rng', 'schema_rng.rng', __DIR__ . "/fixtures/test_sae_schema.rng");
        $connecteurInfo->addFileFromCopy('profil_agape', 'profil_agape.xml', __DIR__ . "/fixtures/test_sae.xml");
        $connecteurInfo->addFileFromData(
            'connecteur_info_content',
            'connecteur_info_content.json',
            json_encode([
                'id_service_versant' => 'FRVERSANT001',
                'id_service_archive' => 'FRAD001',
                'accord_versement' => 'ACCORD001'
            ])
        );

        $info_connecteur = $this->createConnector('fakeSAE', "SAE");
        $this->associateFluxWithConnector($info_connecteur['id_ce'], self::SAE_ONLY, "SAE");

        $info = $this->createDocument(self::SAE_ONLY);
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $donneesFormulaire->setTabData([
            'titre' => 'Foo',
            'date' => '1977-02-18',
            'select' => 'B'
        ]);
        $donneesFormulaire->addFileFromData('fichier', 'fichier.txt', 'bar');
        $donneesFormulaire->addFileFromData('annexe', 'annexe1.txt', 'foo1', 0);
        $donneesFormulaire->addFileFromCopy('annexe', 'annexe2.xml', __DIR__ . "/fixtures/test_sae.xml", 1);
        $donneesFormulaire->addFileFromData('sae_config', "sae_config.json", json_encode(['metadonne1' => 'Ma métadonnées']));

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "orientation")
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $result = $this->triggerActionOnDocument($info['id_d'], "send-archive");
        if (! $result) {
            $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
            echo $donneesFormulaire->getFileContent('sae_bordereau');
        }

        $this->assertLastMessage("Le document a été envoyé au SAE");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);

        $xml = simplexml_load_file($donneesFormulaire->getFilePath('sae_bordereau'));
        $children = $xml->children(SedaValidation::SEDA_V_1_0_NS);
        $children->{'TransferIdentifier'} = "NOT TESTABLE";
        $children->{'Date'} = 'NOT TESTABLE';
        //file_put_contents(__DIR__."/fixtures/bordereau.xml",$xml->asXML());

        $this->assertStringEqualsFile(__DIR__ . "/fixtures/bordereau.xml", $xml->asXML());

        $sae_archive = $donneesFormulaire->getFileContent('sae_archive');

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        file_put_contents("$tmp_folder/archive.tgz", $sae_archive);
        exec("tar xvzf $tmp_folder/archive.tgz -C $tmp_folder");

        $this->assertEquals(
            [
            '.',
            '..',
            'annexe1.txt',
            'annexe2.xml',
            'archive.tgz',
            'fichier.txt',
            'journal.json'
            ],
            scandir("$tmp_folder/")
        );

        $this->assertFileEquals(
            __DIR__ . "/fixtures/test_sae.xml",
            "$tmp_folder/annexe2.xml"
        );

        $this->assertEquals(
            'foo1',
            file_get_contents("$tmp_folder/annexe1.txt")
        );

        $this->assertEquals(
            'bar',
            file_get_contents("$tmp_folder/fichier.txt")
        );

        $tmpFolder->delete($tmp_folder);
    }

    /**
     * @throws DonneesFormulaireException
     * @throws NotFoundException
     * @throws TypeDossierException
     * @throws Exception
     */
    public function testSendArchiveWithSaeError(): void
    {
        $this->typeDossierLoader->createTypeDossierDefinitionFile(self::SAE_ONLY);

        $connector = $this->createConnector('FakeSEDA', 'Bordereau SEDA');
        $this->associateFluxWithConnector($connector['id_ce'], self::SAE_ONLY, 'Bordereau SEDA');
        $connector = $this->createConnector('as@lae-rest', 'SAE');
        $this->associateFluxWithConnector($connector['id_ce'], self::SAE_ONLY, 'SAE');

        $document = $this->createDocument(self::SAE_ONLY);
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->setTabData([
            'titre' => 'Foo',
            'date' => '1977-02-18',
            'select' => 'B'
        ]);
        $donneesFormulaire->addFileFromData('fichier', 'fichier.txt', 'bar');
        $donneesFormulaire->addFileFromData('annexe', 'annexe1.txt', 'foo1', 0);
        $donneesFormulaire->addFileFromCopy('annexe', 'annexe2.xml', __DIR__ . '/fixtures/test_sae.xml', 1);
        $donneesFormulaire->addFileFromData(
            'sae_config',
            "sae_config.json",
            json_encode(['metadonne1' => 'Ma métadonnées'])
        );

        $this->assertTrue(
            $this->triggerActionOnDocument($document['id_d'], 'orientation')
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $this->assertFalse(
            $this->triggerActionOnDocument($document['id_d'], 'send-archive')
        );

        $this->assertLastDocumentAction('erreur-envoie-sae', $document['id_d']);
        $this->assertLastMessage("Erreur de connexion au serveur : <url> malformed - L'envoi du bordereau a échoué : ");
    }
}
