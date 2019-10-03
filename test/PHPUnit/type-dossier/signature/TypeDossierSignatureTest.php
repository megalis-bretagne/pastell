<?php

require_once __DIR__."/../../pastell-core/type-dossier/TypeDossierLoader.class.php";

class TypeDossierSignatureTest extends PastellTestCase {

	const PARAPHEUR_ONLY = 'parapheur-only';
    const PARAPHEUR_CONTINUE_AFTER_REFUSAL = 'parapheur-continue-after-refusal';

    /** @var TypeDossierLoader */
	private $typeDossierLoader;

	/**
	 * @throws Exception
	 */
	public function setUp(){
		parent::setUp();
		$this->typeDossierLoader = $this->getObjectInstancier()->getInstance(TypeDossierLoader::class);
	}

	public function tearDown() {
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
            ]);

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
    public function testEtapeSignature(){
        $info = $this->createConnectorAndDocument(self::PARAPHEUR_ONLY);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);

        $this->assertSame('checked', $donneesFormulaire->get('envoi_signature'));
        $this->assertSame('1', $donneesFormulaire->get('envoi_signature_iparapheur'));
        $this->assertFalse($donneesFormulaire->get('envoi_signature_fast'));

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"orientation")
		);
		$this->assertLastMessage("sélection automatique  de l'action suivante");

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"send-iparapheur")
		);
		$this->assertLastMessage("Le document a été envoyé au parapheur électronique");

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"verif-iparapheur")
		);
		$this->assertLastMessage("La signature a été récupérée");

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"orientation")
		);
		$this->assertLastMessage("sélection automatique  de l'action suivante");

		$this->assertLastDocumentAction('termine',$info['id_d']);
	}


    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function testContinueProgressionAfterRefusal(){
        $info = $this->createConnectorAndDocument(
            self::PARAPHEUR_CONTINUE_AFTER_REFUSAL,
            ['iparapheur_retour' => 'Rejet']
        );

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'],'orientation')
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'],'send-iparapheur')
        );
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'],'verif-iparapheur')
        );

        $this->assertLastMessage('[RejetVisa]');
        $this->assertLastDocumentAction('rejet-iparapheur', $info['id_d']);

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'],'orientation')
        );
        $this->assertLastMessage("sélection automatique  de l'action suivante");

        $this->assertLastDocumentAction('termine',$info['id_d']);
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
        $this->assertFalse($donneesFormulaire->get('envoi_signature_iparapheur'));
        $this->assertSame('1', $donneesFormulaire->get('envoi_signature_fast'));
    }
}