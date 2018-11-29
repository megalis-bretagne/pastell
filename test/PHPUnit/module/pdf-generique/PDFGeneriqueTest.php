<?php


class PDFGeneriqueTest extends PastellTestCase {

	const FILENAME = "Délib Libriciel.pdf";
	const ANNEXE1 = "Annexe1 Délib.pdf";
	const ANNEXE2 = "Annexe2 Délib.pdf";
	const SIGNATURE_ENVOIE = "send-iparapheur";

	/**
	 * @throws Exception
	 */
	public function testCasNominal() {

		$result = $this->getInternalAPI()->post(
			"/entite/" . self::ID_E_COL . "/connecteur",
			array('libelle' => 'Signature', 'id_connecteur' => 'fakeIparapheur')
		);

		$id_ce = $result['id_ce'];

		$this->getInternalAPI()->post(
			"/entite/" . self::ID_E_COL . "/flux/pdf-generique/connecteur/$id_ce",
			array('type' => 'signature')
		);

		$result = $this->getInternalAPI()->post(
			"/Document/" . PastellTestCase::ID_E_COL, array('type' => 'pdf-generique')
		);
		$id_d = $result['id_d'];

		$this->getInternalAPI()->patch(
			"/entite/1/document/$id_d",
			array('libelle' => 'Test pdf générique',
				'envoi_signature' => '1',
				'iparapheur_type' => 'Actes',
				'iparapheur_sous_type' => 'Délibération',
			)
		);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		$donneesFormulaire->addFileFromCopy('document', self::FILENAME, __DIR__ . "/fixtures/" . self::FILENAME, 0);
		$donneesFormulaire->addFileFromCopy('annexe', self::ANNEXE1, __DIR__ . "/fixtures/" . self::ANNEXE1, 0);
		$donneesFormulaire->addFileFromCopy('annexe', self::ANNEXE2, __DIR__ . "/fixtures/" . self::ANNEXE2, 1);

		$actionExecutorFactory = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);
		$actionExecutorFactory->executeOnDocument(1, 0, $id_d, self::SIGNATURE_ENVOIE);

		$this->assertEquals(
			"Le document a été envoyé au parapheur électronique",
			$this->getObjectInstancier()->getInstance('ActionExecutorFactory')->getLastMessage()
		);

	}
}