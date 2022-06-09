<?php

class TypeDossierDepotEtapeTest extends PastellTestCase
{
    public const GED_ONLY = 'ged-only';
    public const STEP_CHECKED_BY_DEFAULT = 'step-checked-by-default';

    /** @var TypeDossierLoader */
    private $typeDossierLoader;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->typeDossierLoader = $this->getObjectInstancier()->getInstance(TypeDossierLoader::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->typeDossierLoader->unload();
    }

    /**
     * @throws Exception
     */
    public function testDepot()
    {
        $this->typeDossierLoader->createTypeDossierDefinitionFile(self::GED_ONLY);


        $info_connecteur = $this->createConnector("FakeGED", "Bouchon GED");
        $this->associateFluxWithConnector($info_connecteur['id_ce'], self::GED_ONLY, "GED");

        $info = $this->createDocument(self::GED_ONLY);
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $donneesFormulaire->setTabData(['metadata1' => 'Foo']);
        $donneesFormulaire->addFileFromData('fichier1', 'fichier1.txt', 'bar');

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "orientation")
        );
        $this->assertLastMessage("sélection automatique de l'action suivante");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "send-ged")
        );
        $this->assertLastMessage("Le dossier Foo a été versé sur le dépôt");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "orientation")
        );
        $this->assertLastMessage("sélection automatique de l'action suivante");

        $this->assertLastDocumentAction('termine', $info['id_d']);
    }

    /**
     * @throws TypeDossierException
     * @throws NotFoundException
     */
    public function testStepIsCheckedByDefault(): void
    {
        $this->typeDossierLoader->createTypeDossierDefinitionFile(self::STEP_CHECKED_BY_DEFAULT);
        $document = $this->createDocument(self::STEP_CHECKED_BY_DEFAULT);
        $this->assertSame('checked', $this->getDonneesFormulaireFactory()->get($document['id_d'])->get('envoi_depot'));
        $this->configureDocument($document['id_d'], ['envoi_depot' => false]);
        $this->assertSame('', $this->getDonneesFormulaireFactory()->get($document['id_d'])->get('envoi_depot'));
    }
}
