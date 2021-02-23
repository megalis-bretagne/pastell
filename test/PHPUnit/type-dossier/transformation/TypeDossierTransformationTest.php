<?php

require_once __DIR__ . "/../../pastell-core/type-dossier/TypeDossierLoader.class.php";

class TypeDossierTransformationTest extends PastellTestCase
{
    public const TRANSFORMATION = 'studio-transformation';
    public const PATH_CONFIG_JSON = __DIR__ . "/../../connecteur/transformation-generique/fixtures/definition.json";

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
     * @param string $typeDossierId
     * @param string $pathJsonConfig
     * @return array
     * @throws DonneesFormulaireException
     * @throws NotFoundException
     * @throws TypeDossierException
     */
    private function createConnectorAndDocument(string $typeDossierId, string $pathJsonConfig): array
    {
        $this->typeDossierLoader->createTypeDossierDefinitionFile($typeDossierId);

        $info_connecteur = $this->createConnector("transformation-generique", "Transformation");
        $connecteurConfig = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($info_connecteur['id_ce']);
        $connecteurConfig->addFileFromCopy(
            'definition',
            "definition.json",
            $pathJsonConfig
        );
        $this->associateFluxWithConnector($info_connecteur['id_ce'], $typeDossierId, "transformation");

        $info_connecteur = $this->createConnector("fakeIparapheur", "Bouchon i-parapheur");
        $this->configureConnector(
            $info_connecteur['id_ce'],
            [
                'iparapheur_type' => 'PADES',
                'iparapheur_envoi_status' => 'ok',
                'iparapheur_retour' => 'Archive',
                'iparapheur_temps_reponse' => 0
            ]
        );
        $this->associateFluxWithConnector($info_connecteur['id_ce'], $typeDossierId, "signature");

        $info = $this->createDocument($typeDossierId);
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $donneesFormulaire->setTabData(['titre' => 'Foo', 'envoi_transformation' => 'true']);
        return $info;
    }

    /**
     * @throws DonneesFormulaireException
     * @throws NotFoundException
     * @throws TypeDossierException
     */
    public function testEtapeTransformationNotValidateByOnChange()
    {
        // transformation avec "envoi_signature": "true"
        $info = $this->createConnectorAndDocument(self::TRANSFORMATION, self::PATH_CONFIG_JSON);

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "orientation")
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $this->assertFalse($donneesFormulaire->get('envoi_signature'));

        $this->assertFalse(
            $this->triggerActionOnDocument($info['id_d'], "transformation")
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $this->assertTrue($donneesFormulaire->get('envoi_signature'));

        $this->assertLastMessage("[transformation] Le dossier n'est pas valide : Le formulaire est incomplet : le champ «Sous-type i-Parapheur» est obligatoire.");

        $this->assertLastDocumentAction('fatal-error', $info['id_d']);
    }

    /**
     * @throws DonneesFormulaireException
     * @throws NotFoundException
     * @throws TypeDossierException
     */
    public function testEtapeTransformationValidateByOnChange()
    {
        // transformation avec "envoi_signature": "true"
        $info = $this->createConnectorAndDocument(self::TRANSFORMATION, self::PATH_CONFIG_JSON);

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "orientation")
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $this->assertFalse($donneesFormulaire->get('envoi_signature'));
        $donneesFormulaire->setData('iparapheur_type', 'PADES');
        $donneesFormulaire->setData('iparapheur_sous_type', 'Document');

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "transformation")
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $this->assertTrue($donneesFormulaire->get('envoi_signature'));

        $this->assertLastMessage("Transformation terminée");

        $this->assertLastDocumentAction('transformation', $info['id_d']);
    }
}
