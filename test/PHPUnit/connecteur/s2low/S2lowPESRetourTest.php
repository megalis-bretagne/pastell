<?php

class S2lowPESRetourTest extends PastellTestCase
{
    /**
     * @throws S2lowException
     * @throws Exception
     */
    public function testPESRetourList()
    {
        $curlWrapper = $this->createMock(CurlWrapper::class);

        $curlWrapper
            ->method('get')
            ->willReturnCallback(function ($url) {

                if ($url == "/admin/users/api-list-login.php") {
                    return true;
                }
                if ($url == "/modules/helios/api/helios_get_list.php") {
                    return file_get_contents(__DIR__ . "/fixtures/pes_retour_liste.xml");
                }
                if (preg_match("#/modules/helios/api/helios_get_retour.php#", $url)) {
                    return "<pes_retour></pes_retour>";
                }
                if (preg_match("#/modules/helios/api/helios_change_status.php#", $url)) {
                    return file_get_contents(__DIR__ . "/fixtures/pes_retour_change.xml");
                }
                throw new UnrecoverableException($url . " inconnu");
            });

        $curlWrapperFactory = $this->createMock(CurlWrapperFactory::class);

        $curlWrapperFactory
            ->method('getInstance')
            ->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $id_ce = $this->createConnector('s2low', "Connecteur S2low")['id_ce'];

        /** @var S2low $s2low */
        $s2low = $this->getConnecteurFactory()->getConnecteurById($id_ce);

        $s2low->getPESRetourListe();

        $documentSQL = $this->getObjectInstancier()->getInstance(DocumentSQL::class);

        $info = $documentSQL->getAllByType(S2low::FLUX_PES_RETOUR);


        $this->assertEquals('PES_AAA.122', $info[0]['titre']);
        $this->assertEquals('PES_AAA.123', $info[1]['titre']);

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($info[0]['id_d']);
        $this->assertEquals(
            '<pes_retour></pes_retour>',
            $donnesFormulaire->getFileContent('fichier_pes')
        );
    }

    /**
     * @throws NotFoundException
     * @throws S2lowException
     */
    public function testPesRetourCreation()
    {
        $curlWrapper = $this->createMock(CurlWrapper::class);

        $curlWrapper
            ->method('get')
            ->willReturn("<pes_retour></pes_retour>");

        $curlWrapperFactory = $this->createMock(CurlWrapperFactory::class);

        $curlWrapperFactory
            ->method('getInstance')
            ->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $id_ce = $this->createConnector('s2low', "Connecteur S2low")['id_ce'];

        /** @var S2low $s2low */
        $s2low = $this->getConnecteurFactory()->getConnecteurById($id_ce);

        $this->assertTrue(
            $s2low->getPESRetour([
                'nom' => 'pes.xml',
                'date' => '2019/07/16',
                'id' => '1234'
            ])
        );

        $documentSQL = $this->getObjectInstancier()->getInstance(DocumentSQL::class);
        $documents = $documentSQL->getAllByType(S2low::FLUX_PES_RETOUR);

        $this->assertEquals('pes', $documents[0]['titre']);

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($documents[0]['id_d']);

        $this->assertSame(
            '1',
            $donnesFormulaire->get('envoi_ged')
        );
    }
}
