<?php

class SAEVerifierTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;


    /**
     * @throws NotFoundException
     */
    public function testVerifier()
    {

        $this->mockCurl([
            '/sedaMessages/sequence:ArchiveTransfer/message:Acknowledgement/originOrganizationIdentification:/originMessageIdentifier:15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421'
            => file_get_contents(__DIR__ . "/fixtures/acuse-de-reception-asalae.xml")
        ]);

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
    public function testVerifierSEDAV02()
    {

        $this->mockCurl([
            '/sedaMessages/sequence:ArchiveTransfer/message:Acknowledgement/originOrganizationIdentification:/originMessageIdentifier:15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421'
            => file_get_contents(__DIR__ . "/fixtures/accuse-de-reception-0.2.xml")
        ]);

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
        $this->assertLastMessage("Récupération de l'accusé de réception : ArchiveTransferReply - Votre transfert d'archive a été pris en compte par la plate-forme as@lae");
        $this->assertLastDocumentAction('ar-recu-sae', $id_d);

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEquals('ACK_unknow.xml', $donnesFormulaire->getFileName('ar_sae'));
        $this->assertEquals(
            'Votre transfert d\'archive a été pris en compte par la plate-forme as@lae',
            $donnesFormulaire->get('sae_ack_comment')
        );
    }
}
