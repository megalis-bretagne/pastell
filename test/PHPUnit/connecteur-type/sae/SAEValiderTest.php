<?php

class SAEValiderTest extends PastellTestCase
{

	use CurlUtilitiesTestTrait;

	/**
	 * @throws NotFoundException
	 */
	public function testValider()
	{
		$this->mockCurl([
			'/sedaMessages/sequence:ArchiveTransfer/message:ArchiveTransferReply/originOrganizationIdentification:/originMessageIdentifier:15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421'
			=> file_get_contents(__DIR__."/fixtures/atr.xml")
		]);

		$id_ce = $this->createConnector('as@lae-rest',"Asalae")['id_ce'];
		$this->associateFluxWithConnector($id_ce,"actes-generique","SAE");

		$id_d = $this->createDocument('actes-generique')['id_d'];
		$donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		$donnesFormulaire->setTabData([
			'sae_transfert_id' => '15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421'
		]);

		$this->getObjectInstancier()
			->getInstance(ActionChange::class)
			->addAction($id_d,self::ID_E_COL,0,"verif-sae","test");

		$this->triggerActionOnDocument($id_d,'validation-sae');
		$this->assertLastDocumentAction('accepter-sae',$id_d);

		$donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		$this->assertEquals("160",$donnesFormulaire->get('sae_archival_identifier'));
		$this->assertEquals('ATR_213.xml',$donnesFormulaire->getFileName('reply_sae'));
		$this->assertEquals(
			'Votre transfert d\'archive a été accepté par la plate-forme as@lae',
			$donnesFormulaire->get('sae_atr_comment')
		);
	}
}