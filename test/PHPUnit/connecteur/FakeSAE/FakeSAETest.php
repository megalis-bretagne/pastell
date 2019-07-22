<?php

class FakeSAETest extends PastellTestCase  {

	public function testARIsCorrect(){
		$id_ce = $this->createConnector('fakeSAE',"Bouchon SAE")['id_ce'];
		$this->associateFluxWithConnector($id_ce,"actes-generique","SAE");

		$id_ce = $this->createConnector('FakeSEDA',"Bouchon SEDA")['id_ce'];
		$this->associateFluxWithConnector($id_ce,"actes-generique","Bordereau SEDA");

		$id_d = $this->createDocument('actes-generique')['id_d'];
		$donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		$donnesFormulaire->setTabData([
			'acte_nature'=>3,
			'envoi_sae'=>'On',
		]);

		$this->triggerActionOnDocument($id_d,'send-archive');

		$this->triggerActionOnDocument($id_d,'verif-sae');

		$this->assertLastDocumentAction('ar-recu-sae',$id_d);

		$donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		$this->assertRegExp('#ACK_\d*\.xml#',$donnesFormulaire->getFileName('ar_sae'));
		$this->assertEquals(
			'Ce transfert d\'archive a été envoyé à un connecteur bouchon SAE !' ,
			$donnesFormulaire->get('sae_ack_comment')
		);
	}

}