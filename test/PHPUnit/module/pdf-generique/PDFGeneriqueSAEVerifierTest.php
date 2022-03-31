<?php

class PDFGeneriqueSAEVerifierTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testCasNominal()
    {
        $curlWrapper = $this->createMock('CurlWrapper');

        $curlWrapper
            ->method('get')
            ->willReturnCallback(function ($a) {
                if ($a == '/sedaMessages/sequence:ArchiveTransfer/message:Acknowledgement/originOrganizationIdentification:Pastell_API/originMessageIdentifier:15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421') {
                    return file_get_contents(__DIR__ . "/fixtures/acuse-de-reception-asalae.xml");
                }
                throw new Exception("Appel à une URL inatendue");
            });

        $curlWrapper
            ->method('getHTTPCode')
            ->willReturn(200);

        $curlWrapperFactory = $this->createMock('CurlWrapperFactory');

        $curlWrapperFactory
            ->method('getInstance')
            ->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $result = $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur",
            ['libelle' => 'SAE', 'id_connecteur' => 'as@lae-rest']
        );

        $id_ce = $result['id_ce'];


        $this->getInternalAPI()->patch(
            "/entite/1/connecteur/$id_ce/content",
            ['originating_agency' => 'SERVICE_VERSANT_PHPUNIT']
        )
        ;

        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/flux/pdf-generique/connecteur/$id_ce",
            ['type' => 'SAE']
        );

        $result = $this->getInternalAPI()->post(
            "/Document/" . PastellTestCase::ID_E_COL,
            ['type' => 'pdf-generique']
        );
        $id_d = $result['id_d'];

        $this->getInternalAPI()->patch(
            "/entite/1/document/$id_d",
            [
                'libelle' => 'Test pdf générique',
                'envoi_sae' => '1',
                'sae_transfert_id' => '15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421'
            ]
        );
        $this->getDonneesFormulaireFactory()->get($id_d)->addFileFromCopy(
            'sae_bordereau',
            'bordereau.xml',
            __DIR__ . "/fixtures/bordereau.xml"
        );
        $this->getInternalAPI()->patch(
            "/entite/1/document/$id_d",
            [
                'libelle' => 'Test pdf générique',
                'envoi_sae' => '1',
                'sae_transfert_id' => '15ef78ef665a8777c33d1125783707f8dfb190f82869dc9248e46c5ed396d70b_1542893421'
            ]
        );

        $actionExecutorFactory = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);
        $actionExecutorFactory->executeOnDocument(1, 0, $id_d, 'verif-sae');

        $this->assertEquals(
            "Récupération de l'accusé de réception : Acknowledgement - Votre transfert d'archive a été pris en compte par la plate-forme as@lae",
            $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class)->getLastMessage()
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertFileEquals(
            __DIR__ . "/fixtures/acuse-de-reception-asalae.xml",
            $donneesFormulaire->getFilePath('ar_sae')
        );

        $documentActionEntite = $this->getObjectInstancier()->getInstance(DocumentActionEntite::class);
        $this->assertEquals('ar-recu-sae', $documentActionEntite->getLastAction(1, $id_d));
    }


    /**
     * @throws Exception
     */
    public function testCasIdTransfertNotAvailable()
    {
        $result = $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur",
            ['libelle' => 'SAE', 'id_connecteur' => 'as@lae-rest']
        );

        $id_ce = $result['id_ce'];


        $this->getInternalAPI()->patch(
            "/entite/1/connecteur/$id_ce/content",
            ['originating_agency' => 'SERVICE_VERSANT_PHPUNIT']
        )
        ;

        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/flux/pdf-generique/connecteur/$id_ce",
            ['type' => 'SAE']
        );

        $result = $this->getInternalAPI()->post(
            "/Document/" . PastellTestCase::ID_E_COL,
            ['type' => 'pdf-generique']
        );
        $id_d = $result['id_d'];

        $this->getInternalAPI()->patch(
            "/entite/1/document/$id_d",
            [
                'libelle' => 'Test pdf générique',
                'envoi_sae' => '1',
            ]
        );

        $this->getDonneesFormulaireFactory()->get($id_d)->addFileFromCopy(
            'sae_bordereau',
            'bordereau.xml',
            __DIR__ . "/fixtures/bordereau.xml"
        );

        $actionExecutorFactory = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);
        $actionExecutorFactory->executeOnDocument(1, 0, $id_d, 'verif-sae');

        $this->assertEquals(
            "L'identifiant du transfert n'a pas été trouvé",
            $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class)->getLastMessage()
        );

        $documentActionEntite = $this->getObjectInstancier()->getInstance(DocumentActionEntite::class);
        $this->assertEquals('verif-sae-erreur', $documentActionEntite->getLastAction(1, $id_d));
    }


    /**
     * @throws Exception
     */
    public function testCasNonDisponiblel()
    {
        $curlWrapper = $this->createMock('CurlWrapper');

        $curlWrapper
            ->method('get')
            ->willReturnCallback(function ($a) {
                if ($a == '/sedaMessages/sequence:ArchiveTransfer/message:Acknowledgement/originOrganizationIdentification:Pastell_API/originMessageIdentifier:mon_id_de_transfert_phpunit') {
                    return 'pas disponible erreur 500';
                }
                throw new Exception("Appel à une URL inatendue");
            });

        $curlWrapper
            ->method('getHTTPCode')
            ->willReturn(500);

        $curlWrapperFactory = $this->createMock('CurlWrapperFactory');

        $curlWrapperFactory
            ->method('getInstance')
            ->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $result = $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur",
            ['libelle' => 'SAE', 'id_connecteur' => 'as@lae-rest']
        );

        $id_ce = $result['id_ce'];


        $this->getInternalAPI()->patch(
            "/entite/1/connecteur/$id_ce/content",
            ['originating_agency' => 'SERVICE_VERSANT_PHPUNIT']
        )
        ;

        $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/flux/pdf-generique/connecteur/$id_ce",
            ['type' => 'SAE']
        );

        $result = $this->getInternalAPI()->post(
            "/Document/" . PastellTestCase::ID_E_COL,
            ['type' => 'pdf-generique']
        );
        $id_d = $result['id_d'];

        $this->getInternalAPI()->patch(
            "/entite/1/document/$id_d",
            [
                'libelle' => 'Test pdf générique',
                'envoi_sae' => '1',
                'sae_transfert_id' => 'mon_id_de_transfert_phpunit'
            ]
        );

        $this->getDonneesFormulaireFactory()->get($id_d)->addFileFromCopy(
            'sae_bordereau',
            'bordereau.xml',
            __DIR__ . "/fixtures/bordereau.xml"
        );

        $actionExecutorFactory = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);
        $actionExecutorFactory->executeOnDocument(1, 0, $id_d, 'verif-sae');

        $this->assertEquals(
            "pas disponible erreur 500 - code d'erreur HTTP : 500",
            $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class)->getLastMessage()
        );

        $documentActionEntite = $this->getObjectInstancier()->getInstance(DocumentActionEntite::class);
        $this->assertEquals('modification', $documentActionEntite->getLastAction(1, $id_d));
    }
}
