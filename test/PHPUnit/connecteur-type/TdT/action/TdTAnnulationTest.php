<?php

class TdTAnnulationTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testCancellation()
    {
        $connector = $this->createConnector('fakeTdt', 'fake tdt');
        $this->associateFluxWithConnector($connector['id_ce'], 'actes-generique', 'TdT');

        $document = $this->createDocument('actes-generique');
        $id_d = $document['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromData('arrete', 'arrete.pdf', '%PDF1-4');
        $this->configureDocument($id_d, [
            'date_de_lacte' => '2019-12-18',
            'acte_nature' => 3,
            'objet' => 'objet',
            'numero_de_lacte' => '201912181628',
            'classification' => '1.1',
            'type_piece' => 'ok',
            'type_acte' => '99_AI',
            'type_pj' => '[]',
            'envoi_tdt' => true,
        ]);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, 'send-tdt')
        );
        $this->assertLastMessage('Le document a été envoyé au contrôle de légalité');

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, 'verif-tdt')
        );
        $this->assertLastMessage("L'acquittement du contrôle de légalité a été reçu.");

        $connector = $this->createConnector('s2low', 's2low');
        $this->configureConnector($connector['id_ce'], [
            'url' => '',
            'authentication_for_teletransmisson' => true
        ]);
        $this->associateFluxWithConnector($connector['id_ce'], 'actes-generique', 'TdT');
        $this->mockCurl([
            '/admin/users/api-list-login.php' => true,
            '/modules/actes/actes_transac_cancel.php' => "OK\n1234",
            '/modules/actes/actes_transac_get_status.php?transaction=1234' =>
                "OK\n4\n" . file_get_contents(__DIR__ . '/../fixtures/ACTE-ar-annulation.xml')
        ]);
        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, 'annulation-tdt')
        );
        $this->assertLastMessage("Une notification d'annulation a été envoyée au contrôle de légalité");
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertSame('1234', $donneesFormulaire->get('tedetis_annulation_id'));

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, 'verif-annulation-tdt')
        );
        $this->assertStringEqualsFile(
            __DIR__ . '/../fixtures/ACTE-ar-annulation.xml',
            $donneesFormulaire->getFileContent('aractes_annulation')
        );
        $this->assertLastMessage("L'acquittement pour l'annulation de l'acte a été reçu.");
    }
}
