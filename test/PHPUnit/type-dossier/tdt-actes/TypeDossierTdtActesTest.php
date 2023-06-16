<?php

class TypeDossierTdtActesTest extends PastellTestCase
{
    public const TDT_ACTES_ONLY = 'tdt-actes-only';

    private TypeDossierLoader $typeDossierLoader;
    private string $dataDir;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->typeDossierLoader = $this->getObjectInstancier()->getInstance(TypeDossierLoader::class);
        $this->dataDir = $this->getObjectInstancier()->getInstance('data_dir');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->typeDossierLoader->unload();
    }

    /**
     * @throws TypeDossierException
     * @throws NotFoundException
     * @throws DonneesFormulaireException
     * @throws Exception
     */
    public function testEtapeTdtActes(): void
    {
        $this->typeDossierLoader->createTypeDossierDefinitionFile(self::TDT_ACTES_ONLY);

        $info_connecteur = $this->createConnector('fakeTdt', 'Bouchon Tdt');

        $connecteurInfo = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire(
            $info_connecteur['id_ce']
        );

        $connecteurInfo->addFileFromCopy(
            'classification_file',
            'classifiction.xml',
            $this->dataDir . '/connector/fakeTdt/classification.xml'
        );

        $this->associateFluxWithConnector($info_connecteur['id_ce'], self::TDT_ACTES_ONLY, 'TdT');

        $info = $this->createDocument(self::TDT_ACTES_ONLY);
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $donneesFormulaire->setTabData(['titre' => 'Foo']);
        $donneesFormulaire->addFileFromData('fichier', 'fichier.txt', 'bar');

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "orientation")
        );
        $this->assertLastMessage("sélection automatique de l'action suivante");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "send-tdt")
        );
        $this->assertLastMessage("Le document a été envoyé au contrôle de légalité");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "verif-tdt")
        );
        $this->assertLastMessage("L'acquittement du contrôle de légalité a été reçu.");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "orientation")
        );
        $this->assertLastMessage("sélection automatique de l'action suivante");

        $this->assertLastDocumentAction('termine', $info['id_d']);
    }
}
