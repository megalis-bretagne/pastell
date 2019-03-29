<?php

require_once __DIR__."/../../pastell-core/type-dossier/TypeDossierLoader.class.php";

class TypeDossierDepotEtapeTest extends PastellTestCase {

	const GED_ONLY = 'ged_only';

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
	public function testDepot(){
		$this->typeDossierLoader->createTypeDossierDefinitionFile(self::GED_ONLY);


		$info_connecteur = $this->createConnector("FakeGED","Bouchon GED");
		$this->associateFluxWithConnector($info_connecteur['id_ce'],self::GED_ONLY,"GED");

		$info = $this->createDocument(self::GED_ONLY);
		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
		$donneesFormulaire->setTabData(['metadata1'=>'Foo']);
		$donneesFormulaire->addFileFromData('fichier1','fichier1.txt','bar');

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"orientation")
		);
		$this->assertLastMessage("sélection automatique  de l'action suivante");

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"send-ged")
		);
		$this->assertLastMessage("Le document Foo a été versé sur le dépôt");

		$this->assertTrue(
			$this->triggerActionOnDocument($info['id_d'],"orientation")
		);
		$this->assertLastMessage("sélection automatique  de l'action suivante");

		$this->assertLastDocumentAction('termine',$info['id_d']);
	}

}