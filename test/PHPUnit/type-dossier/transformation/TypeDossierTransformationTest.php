<?php

require_once __DIR__ . "/../../pastell-core/type-dossier/TypeDossierLoader.class.php";
require_once __DIR__ . "/../../../../connecteur/glaneur-sftp/GlaneurSFTP.class.php";

class TypeDossierTransformationTest extends PastellTestCase
{
    public const TRANSFORMATION = 'studio-transformation';
    public const PATH_CONFIG_JSON = __DIR__ . "/../../connecteur/transformation-generique/fixtures/definition.json";

    /** @var TypeDossierLoader */
    private $typeDossierLoader;

    /** @var TmpFolder */
    private $tmpFolder;

    /** @var string */
    private $workspace_path;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();
        $this->typeDossierLoader = $this->getObjectInstancier()->getInstance(TypeDossierLoader::class);
        // pour le glaneur:
        $this->tmpFolder = new TmpFolder();
        $this->workspace_path = $this->tmpFolder->create();
        $this->getObjectInstancier()->setInstance('workspacePath', $this->workspace_path);
    }

    public function tearDown()
    {
        $this->typeDossierLoader->unload();
        $this->tmpFolder->delete($this->workspace_path);
        $this->tmpFolder = null;
        parent::tearDown();
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

    /**
     * @throws DonneesFormulaireException
     * @throws NotFoundException
     * @throws TypeDossierException
     */
    public function testEtapeTransformationAfterGlaneur()
    {
        $this->createConnectorAndDocument(self::TRANSFORMATION, self::PATH_CONFIG_JSON);

        $glaneurSFTP = $this->getObjectInstancier()->getInstance(GlaneurSFTP::class);

        $glaneurSFTP->setConnecteurInfo(['id_e' => self::ID_E_COL]);
        $glaneurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $glaneurConfig->setTabData([
            GlaneurConnecteur::TRAITEMENT_ACTIF => '1',
            GlaneurConnecteur::TYPE_DEPOT => GlaneurConnecteur::TYPE_DEPOT_VRAC,
            GlaneurConnecteur::FILE_PREG_MATCH => 'fichier: /^(.*).xml$/',
            GlaneurConnecteur::METADATA_STATIC =>
                "titre: %fichier%\n
                iparapheur_type: PADES\n
                iparapheur_sous_type: Document",
            GlaneurConnecteur::FLUX_NAME => self::TRANSFORMATION,
            GlaneurConnecteur::ACTION_OK => 'importation',
        ]);
        $glaneurConfig->addFileFromCopy(
            GlaneurConnecteur::FICHER_EXEMPLE,
            'pes.zip',
            __DIR__ . '/../../connecteur/glaneur-sftp/fixtures/HELIOS_SIMU_ALR2_1547544424_844200543.zip'
        );

        $glaneurSFTP->setConnecteurConfig($glaneurConfig);
        $id_d = $glaneurSFTP->glanerFicExemple();
        $this->assertSame("Création du document $id_d", $glaneurSFTP->getLastMessage()[0]);

        $this->triggerActionOnDocument($id_d, "transformation");
        $this->assertLastMessage("Transformation terminée");
    }
}
