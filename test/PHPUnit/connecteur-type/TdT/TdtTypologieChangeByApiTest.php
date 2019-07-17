<?php

class TdtTypologieChangeByApiTest extends PastellTestCase {

	/**
	 * @throws NotFoundException
	 * @throws Exception
	 */
	public function testAddTypeActe(){

		$connecteur_info = $this->createConnector("fakeTdt","Bouchon tdt");

		$connecteur_info['id_ce'];
		$connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()
			->getConnecteurEntiteFormulaire($connecteur_info['id_ce']);

		$connecteurDonneesFormulaire->addFileFromCopy(
			'classification_file',
			"classification.xml",
			__DIR__."/../../module/actes-generique/fixtures/classification.xml"
		);
		$this->associateFluxWithConnector($connecteur_info['id_ce'],"actes-generique","TdT");

		$document_info = $this->createDocument("actes-generique");
		$id_d = $document_info['id_d'];

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

		$donneesFormulaire->setTabData([
			'acte_nature' => '3',
			'numero_de_lacte' => '1515',
		]);

		$donneesFormulaire->addFileFromData('arrete',"arrete.pdf","foo");
		$donneesFormulaire->addFileFromData(
			'autre_document_attache',
			"annexe1.pdf",
			"bar",
			0
		);
		$donneesFormulaire->addFileFromData(
			'autre_document_attache',
			"annexe1.pdf",
			"baz",
			1
		);

		$info = $this->getInternalAPI()->patch("/Entite/1/document/$id_d/",['type_acte'=>'22_NE']);
		$this->assertEquals("22_NE",$info['content']['data']['type_acte']);
		$this->assertEquals("1 fichier(s) typé(s)",$info['content']['data']['type_piece']);
		$this->assertEquals(
			'[{"filename":"arrete.pdf","typologie":"Notice explicative (22_NE)"}]',
			$donneesFormulaire->getFileContent('type_piece_fichier')
		);

		$info = $this->getInternalAPI()->patch("/Entite/1/document/$id_d/",['type_pj'=>'["41_NC","22_DP"]']);
		$this->assertEquals("22_NE",$info['content']['data']['type_acte']);
		$this->assertEquals('["41_NC","22_DP"]',$info['content']['data']['type_pj']);
		$this->assertEquals("3 fichier(s) typé(s)",$info['content']['data']['type_piece']);
		$this->assertEquals(
			'[{"filename":"arrete.pdf","typologie":"Notice explicative (22_NE)"},{"filename":"annexe1.pdf","typologie":"Notification de cr\u00e9ation ou de vacance de poste (41_NC)"},{"filename":"annexe1.pdf","typologie":"Document photographique (22_DP)"}]',
			$donneesFormulaire->getFileContent('type_piece_fichier')
		);
	}
}