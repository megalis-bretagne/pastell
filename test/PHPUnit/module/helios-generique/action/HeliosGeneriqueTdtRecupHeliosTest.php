<?php

class HeliosGeneriqueTdtRecupHeliosTest extends PastellTestCase
{
    /**
     * @throws NotFoundException
     */
    public function testCasNominal()
    {

        $info_connecteur = $this->createConnector("fakeTdt", "Bouchon Tdt");
        $this->associateFluxWithConnector($info_connecteur['id_ce'], "helios-generique", "TdT");

        $info = $this->createDocument("helios-generique");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $donneesFormulaire->setTabData(['objet' => 'Foo','envoi_tdt' => 'On']);
        $donneesFormulaire->addFileFromCopy(
            'fichier_pes',
            'HELIOS_SIMU_ALR2_1496987735_826268894.xml',
            __DIR__ . "/../fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml"
        );


        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "send-tdt")
        );

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "verif-tdt")
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);

        $this->assertEquals(
            'HELIOS_SIMU_ALR2_1496987735_826268894_ACK.xml',
            $donneesFormulaire->getFileName('fichier_reponse')
        );
        $this->assertEquals(1, $donneesFormulaire->get('etat_ack'));
    }

    /**
     * @throws NotFoundException
     */
    public function testWhenErrorOnTdt()
    {


        $curlWrapper = $this->createMock(CurlWrapper::class);

        $curlWrapper->method('get')->willReturnCallback(function ($a) {
            if ($a == "/admin/users/api-list-login.php") {
                return true;
            }
            if ($a == "/modules/helios/api/helios_importer_fichier.php") {
                return file_get_contents(__DIR__ . "/../fixtures/helios-post.xml");
            }
            if ($a == "/modules/helios/api/helios_transac_get_status.php?transaction=1234") {
                return file_get_contents(__DIR__ . "/../fixtures/helios-reponse.xml");
            }
            return true;
        });

        $curlWrapperFactory = $this->createMock(CurlWrapperFactory::class);

        $curlWrapperFactory->method('getInstance')->willReturn($curlWrapper);
        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);


        $info_connecteur = $this->createConnector("s2low", "s2low");

        $this->associateFluxWithConnector($info_connecteur['id_ce'], "helios-generique", "TdT");

        $info = $this->createDocument("helios-generique");

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $donneesFormulaire->setTabData(['objet' => 'Foo','envoi_tdt' => 'On']);
        $donneesFormulaire->addFileFromCopy(
            'fichier_pes',
            'fichier.xml',
            __DIR__ . "/../fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml"
        );

        $this->assertTrue(
            $this->triggerActionOnDocument($info['id_d'], "send-tdt")
        );
        $this->assertFalse(
            $this->triggerActionOnDocument($info['id_d'], "verif-tdt")
        );
        $this->assertLastMessage(
            "Transaction en erreur sur le TdT: Ceci est un message d'erreur avec accent à é ç"
        );
    }
}
