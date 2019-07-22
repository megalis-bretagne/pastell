<?php

class SAEVerifierTest extends PastellTestCase {


	/**
	 * @throws NotFoundException
	 */
	public function testVerifier(){

		$this->mockCurl();

		$id_ce = $this->createConnector('as@lae-rest',"Asalae")['id_ce'];
		$this->associateFluxWithConnector($id_ce,"actes-generique","SAE");

		$id_d = $this->createDocument('actes-generique')['id_d'];
		$donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		$donnesFormulaire->setTabData([
			'sae_transfert_id' => '15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421'
		]);

		$actionChange = $this->getObjectInstancier()->getInstance(ActionChange::class);

		$actionChange->addAction($id_d,self::ID_E_COL,0,"send-archive","test");

		$this->triggerActionOnDocument($id_d,'verif-sae');

		$this->assertLastDocumentAction('ar-recu-sae',$id_d);

		$donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		$this->assertEquals('ACK_258.xml',$donnesFormulaire->getFileName('ar_sae'));
		$this->assertEquals(
			'Votre transfert d\'archive a été pris en compte par la plate-forme as@lae',
			$donnesFormulaire->get('sae_ack_comment')
		);
	}

	private function mockCurl(){
		$curlWrapper = $this->getMockBuilder('CurlWrapper')
			->disableOriginalConstructor()
			->getMock();

		$curlWrapper->expects($this->any())
			->method('get')
			->will($this->returnCallback(function($url){
				if ($url == '/sedaMessages/sequence:ArchiveTransfer/message:Acknowledgement/originOrganizationIdentification:/originMessageIdentifier:15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421'){
					return file_get_contents(__DIR__."/fixtures/acuse-de-reception-asalae.xml");
				}
				throw new UnrecoverableException("Appel à une URL inatendue $url");
			}));

		$curlWrapper->expects($this->any())
			->method('getHTTPCode')
			->willReturn(200);

		$curlWrapperFactory = $this->getMockBuilder('CurlWrapperFactory')
			->disableOriginalConstructor()
			->getMock();

		$curlWrapperFactory->expects($this->any())
			->method('getInstance')
			->willReturn($curlWrapper);

		$this->getObjectInstancier()->setInstance(CurlWrapperFactory::class,$curlWrapperFactory);
	}

}