<?php

class PESMarcheRecupTdtTest extends PastellMarcheTestCase
{

    /**
     * @throws Exception
     */
    public function testRecupTdt()
    {
        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)->getMock();
        $curlWrapper
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($url) {
                if ($url === '/admin/users/api-list-login.php') {
                    return true;
                }
                if ($url === '/modules/helios/api/helios_importer_fichier.php') {
                    return "<import><id>1234</id><resultat>OK</resultat><message> message complémentaire </message></import>";
                }
                if ($url === '/modules/helios/api/helios_transac_get_status.php?transaction=1234') {
                    return "<transaction><id>1234</id><resultat>OK</resultat><status>3</status><message></message></transaction>";
                }

                throw new Exception("$url inatendu");
            });

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)->getMock();
        $curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);


        $s2low = $this->createConnector('s2low', 'S2low');
        $this->associateFluxWithConnector($s2low['id_ce'], 'pes-marche', 'TdT');
        $document = $this->createDocument('pes-marche');
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document);
        $donneesFormulaire->setTabData(
            [
                'objet' => 'foobar',
                'envoi_tdt' => true
            ]
        );
        $donneesFormulaire->addFileFromCopy(
            'fichier_pes',
            'exemple_marche_contrat_initial_nov2017.xml',
            __DIR__ . "/../fixtures/exemple_marche_contrat_initial_nov2017.xml"
        );

        $this->assertTrue(
            $this->triggerActionOnDocument($document, 'send-tdt')
        );
        $this->assertLastMessage('Le document a été envoyé au TdT');

        $this->assertTrue(
            $this->triggerActionOnDocument($document, 'verif-tdt')
        );
        $this->assertLastMessage("La transaction est dans l'état : Transmis (3) ");
    }
}
