<?php

require_once __DIR__."/../../pastell-core/type-dossier/TypeDossierLoader.class.php";

class TypeDossierSignatureTest extends PastellTestCase {

	const PARAPHEUR_ONLY = 'parapheur_only';

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
	 * @throws Exception
	 */
	public function testEtapeSignature(){
		$this->typeDossierLoader->createTypeDossierDefinitionFile(self::PARAPHEUR_ONLY);


		$info_connecteur = $this->createConnector("fakeIparapheur","Bouchon i-parapheur");

		$connecteurInfo = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($info_connecteur['id_ce']);

		$connecteurInfo->setTabData([
			'iparapheur_type' => 'PADES',
			'iparapheur_envoi_status' => 'ok',
			'iparapheur_retour'=>'Archive',
			'iparapheur_temps_reponse'=>0
		]);


		$this->associateFluxWithConnector($info_connecteur['id_ce'],self::PARAPHEUR_ONLY,"signature");

		$info = $this->createDocument(self::PARAPHEUR_ONLY);
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
		$donneesFormulaire->setTabData(['titre'=>'Foo']);
		$donneesFormulaire->addFileFromData('fichier','fichier.txt','bar');

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"orientation")
		);
		$this->assertLastMessage("sélection automatique  de l'action suivante");

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"send-iparapheur")
		);
		$this->assertLastMessage("Le document a été envoyé au parapheur électronique");

		//$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"verif-iparapheur");
		//);
		$this->assertLastMessage("La signature a été récupérée");

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"orientation")
		);
		$this->assertLastMessage("sélection automatique  de l'action suivante");

		$this->assertLastDocumentAction('termine',$info['id_d']);
	}
}