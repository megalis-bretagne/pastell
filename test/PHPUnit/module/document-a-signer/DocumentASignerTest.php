<?php

class DocumentASignerTest extends PastellTestCase {


	/**
	 * @throws Exception
	 */
	public function testCasNominal(){

		$connecteur_info = $this->createConnector('fakeIparapheur',"Bouchon parapheur");

		$this->getDonneesFormulaireFactory()
			->getConnecteurEntiteFormulaire($connecteur_info['id_ce'])
			->setTabData([
				'iparapheur_type'=>'Document',
				'iparapheur_envoi_status' => 'ok',
				'iparapheur_retour' => 'Archive'
			]);

		$this->associateFluxWithConnector($connecteur_info['id_ce'],'document-a-signer','signature');

		$connecteur_info = $this->createConnector('FakeGED',"Bouchon GED");
		$this->associateFluxWithConnector($connecteur_info['id_ce'],'document-a-signer','GED');

		$document_info = $this->createDocument('document-a-signer');

		$id_d = $document_info['id_d'];

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

		$donneesFormulaire->setTabData([
			'libelle' => 'Test',
			'envoi_ged' => "On",
			"envoi_auto" => "On",
			"iparapheur_type" => "test",
			"iparapheur_sous_type"=>"test"
		]);

		$donneesFormulaire->addFileFromData('document','document.txt',"foo");

		$actionPossible = $this->getObjectInstancier()->getInstance(ActionPossible::class);
		$this->assertEquals(
			['modification','supression','send-iparapheur'],
			$actionPossible->getActionPossible(self::ID_E_COL,0,$id_d)
		);

		$this->assertTrue(
			$this->triggerActionOnDocument($id_d,"send-iparapheur")
		);

		$this->assertLastDocumentAction('send-iparapheur',$id_d);

		$this->assertTrue(
			$this->triggerActionOnDocument($id_d,"verif-iparapheur")
		);
		$this->assertLastDocumentAction('recu-iparapheur',$id_d);

		$this->assertTrue(
			$this->triggerActionOnDocument($id_d,"send-ged")
		);

		$this->assertLastDocumentAction('send-ged',$id_d);
	}
}