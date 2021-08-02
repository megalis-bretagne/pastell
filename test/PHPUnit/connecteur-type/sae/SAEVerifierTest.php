<?php

class SAEVerifierTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;

    private const MESSAGE_ACK = '/sedaMessages/sequence:ArchiveTransfer/message:Acknowledgement/originOrganizationIdentification:/originMessageIdentifier:15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421';

    /**
     * @throws NotFoundException
     */
    public function testVerifierSEDAV0_2()
    {
        $this->mockCurlWithAckResponse(__DIR__ . "/fixtures/ACK-SEDA-0.2.xml");

        $id_d = $this->retrieveAck();
        $this->assertLastMessage("Récupération de l'accusé de réception : ArchiveTransferReply - Votre transfert d'archive a été pris en compte par la plate-forme as@lae");

        $this->assertLastDocumentAction('ar-recu-sae', $id_d);


        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEquals('ATR_77.xml', $donnesFormulaire->getFileName('ar_sae'));
        $this->assertEquals(
            'Votre transfert d\'archive a été pris en compte par la plate-forme as@lae',
            $donnesFormulaire->get('sae_ack_comment')
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testVerifierSEDAv1_0()
    {

        $this->mockCurlWithAckResponse(__DIR__ . "/fixtures/ACK-SEDA-1.0.xml");

        $id_d = $this->retrieveAck();
        $this->assertLastDocumentAction('ar-recu-sae', $id_d);

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEquals('ACK_258.xml', $donnesFormulaire->getFileName('ar_sae'));
        $this->assertEquals(
            'Votre transfert d\'archive a été pris en compte par la plate-forme as@lae',
            $donnesFormulaire->get('sae_ack_comment')
        );
    }


    /**
     * @throws NotFoundException
     */
    public function testVerifierSEDAv2_1()
    {

        $this->mockCurlWithAckResponse(__DIR__ . "/fixtures/ACK-SEDA-2.1.xml");

        $id_d = $this->retrieveAck();

        $this->assertLastMessage("Récupération de l'accusé de réception : Acknowledgement - Versement flux test pastell dev");

        $this->assertLastDocumentAction('ar-recu-sae', $id_d);

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEquals('FRAD000_ACK_14.xml', $donnesFormulaire->getFileName('ar_sae'));
        $this->assertEquals(
            'Versement flux test pastell dev',
            $donnesFormulaire->get('sae_ack_comment')
        );
    }

    private function mockCurlWithAckResponse($ack_response_filename)
    {
        $this->mockCurl([
            self::MESSAGE_ACK
            => file_get_contents($ack_response_filename)
        ]);
    }

    /**
     * @return string
     * @throws NotFoundException
     */
    private function retrieveAck(): string
    {
        $id_ce = $this->createConnector('as@lae-rest', "Asalae")['id_ce'];
        $this->associateFluxWithConnector($id_ce, "actes-generique", "SAE");

        $id_d = $this->createDocument('actes-generique')['id_d'];
        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donnesFormulaire->setTabData([
            'sae_transfert_id' => '15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421'
        ]);

        $actionChange = $this->getObjectInstancier()->getInstance(ActionChange::class);

        $actionChange->addAction($id_d, self::ID_E_COL, 0, "send-archive", "test");

        $this->triggerActionOnDocument($id_d, 'verif-sae');
        return $id_d;
    }
}
