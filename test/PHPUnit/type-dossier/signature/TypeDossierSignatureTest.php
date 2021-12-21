<?php

require_once __DIR__ . "/../../pastell-core/type-dossier/TypeDossierLoader.class.php";

class TypeDossierSignatureTest extends PastellTestCase
{
    public const PARAPHEUR_ONLY = 'parapheur-only';
    public const PARAPHEUR_CONTINUE_AFTER_REFUSAL = 'parapheur-continue-after-refusal';
    public const DOUBLE_PARAPHEUR = 'double-parapheur';

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
     * @param array $connectorConfig
     * @return array
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    private function createConnectorAndDocument(string $typeDossierId, array $connectorConfig = []): array
    {
        $this->typeDossierLoader->createTypeDossierDefinitionFile($typeDossierId);

        $info_connecteur = $this->createConnector("fakeIparapheur", "Bouchon i-parapheur");

        $this->configureConnector(
            $info_connecteur['id_ce'],
            $connectorConfig + [
                'iparapheur_type' => 'PADES',
                'iparapheur_envoi_status' => 'ok',
                'iparapheur_retour' => 'Archive',
                'iparapheur_temps_reponse' => 0
            ]
        );

        $this->associateFluxWithConnector($info_connecteur['id_ce'], $typeDossierId, "signature");

        $info = $this->createDocument($typeDossierId);
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $donneesFormulaire->setTabData(['titre' => 'Foo']);
        $donneesFormulaire->addFileFromData('fichier', 'fichier.txt', 'bar');
        return $info;
    }


    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function testEtapeSignature()
    {
        $info = $this->createConnectorAndDocument(self::PARAPHEUR_ONLY);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);

        $this->assertSame('checked', $donneesFormulaire->get('envoi_signature'));
        $this->assertSame('1', $donneesFormulaire->get('envoi_iparapheur'));
        $this->assertSame('', $donneesFormulaire->get('envoi_fast'));

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "orientation")
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "send-iparapheur")
        );
        $this->assertLastMessage("Le document a été envoyé au parapheur électronique");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "verif-iparapheur")
        );
        $this->assertLastMessage("La signature a été récupérée");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "orientation")
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $this->assertLastDocumentAction('termine', $info['id_d']);
    }


    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function testContinueProgressionAfterRefusal()
    {
        $info = $this->createConnectorAndDocument(
            self::PARAPHEUR_CONTINUE_AFTER_REFUSAL,
            ['iparapheur_retour' => 'Rejet']
        );

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'orientation')
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'send-iparapheur')
        );
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'verif-iparapheur')
        );

        $this->assertLastMessage('[RejetVisa]');
        $this->assertLastDocumentAction('rejet-iparapheur', $info['id_d']);

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'orientation')
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $this->assertLastDocumentAction('termine', $info['id_d']);
    }

    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function testSignatureStepWithFastConnector()
    {
        $info = $this->createConnectorAndDocument(
            self::PARAPHEUR_ONLY,
            ['is_fast' => true]
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);

        $this->assertSame('checked', $donneesFormulaire->get('envoi_signature'));
        $this->assertSame('', $donneesFormulaire->get('envoi_iparapheur'));
        $this->assertSame('1', $donneesFormulaire->get('envoi_fast'));
    }

    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function testDoubleSignatureSteps()
    {
        $info = $this->createConnectorAndDocument(
            self::DOUBLE_PARAPHEUR,
            ['is_fast' => true]
        );

        $secondSignatureConnector = $this->createConnector('fakeIparapheur', 'Bouchon i-parapheur');
        $this->configureConnector($secondSignatureConnector['id_ce'], [
            'iparapheur_type' => 'PADES',
            'iparapheur_envoi_status' => 'ok',
            'iparapheur_retour' => 'Archive',
            'iparapheur_temps_reponse' => 0
        ]);
        $this->associateFluxWithConnector(
            $secondSignatureConnector['id_ce'],
            self::DOUBLE_PARAPHEUR,
            'signature',
            self::ID_E_COL,
            1
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);

        $envoi_signature_1 = 'envoi_signature_1';
        $envoi_signature_2 = 'envoi_signature_2';
        $envoi_iparapheur_1 = 'envoi_iparapheur_1';
        $envoi_iparapheur_2 = 'envoi_iparapheur_2';
        $envoi_fast_1 = 'envoi_fast_1';
        $envoi_fast_2 = 'envoi_fast_2';

        $this->assertSame('checked', $donneesFormulaire->get($envoi_signature_1));
        $this->assertSame('checked', $donneesFormulaire->get($envoi_signature_2));

        $this->configureDocument($info['id_d'], [
            $envoi_signature_1 => false,
            $envoi_signature_2 => false
        ]);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);

        $this->assertSame('', $donneesFormulaire->get($envoi_signature_1));
        $this->assertSame('', $donneesFormulaire->get($envoi_signature_2));
        $this->assertSame('', $donneesFormulaire->get($envoi_iparapheur_1));
        $this->assertSame('', $donneesFormulaire->get($envoi_iparapheur_2));
        $this->assertSame('', $donneesFormulaire->get($envoi_fast_1));
        $this->assertSame('', $donneesFormulaire->get($envoi_fast_2));

        $this->configureDocument($info['id_d'], [
            $envoi_signature_1 => true,
            $envoi_signature_2 => true
        ]);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);

        $this->assertSame('1', $donneesFormulaire->get($envoi_signature_1));
        $this->assertSame('1', $donneesFormulaire->get($envoi_signature_2));
        $this->assertSame('', $donneesFormulaire->get($envoi_iparapheur_1));
        $this->assertSame('1', $donneesFormulaire->get($envoi_iparapheur_2));
        $this->assertSame('1', $donneesFormulaire->get($envoi_fast_1));
        $this->assertSame('', $donneesFormulaire->get($envoi_fast_2));

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'orientation')
        );

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'send-iparapheur_1')
        );
        $this->assertLastMessage("Le document a été envoyé au parapheur électronique");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'verif-iparapheur_1')
        );
        $this->assertLastMessage('La signature a été récupérée');

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'orientation')
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'send-iparapheur_2')
        );
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'verif-iparapheur_2')
        );
        $this->assertLastMessage('La signature a été récupérée');
        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'orientation')
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");
        $this->assertLastDocumentAction('termine', $info['id_d']);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);

        $this->assertSame(
            'iparapheur_historique_1.xml',
            $donneesFormulaire->getFileName('iparapheur_historique_1')
        );
        $this->assertSame(
            'iparapheur_historique_2.xml',
            $donneesFormulaire->getFileName('iparapheur_historique_2')
        );

        $this->assertSame(
            '[Archive]',
            $donneesFormulaire->get('parapheur_last_message_1')
        );
        $this->assertSame(
            '[Archive]',
            $donneesFormulaire->get('parapheur_last_message_2')
        );
    }
}
