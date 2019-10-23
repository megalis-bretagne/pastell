<?php

require_once __DIR__ . "/../../../../connecteur/fast-parapheur/FastParapheur.class.php";

class FastParapheurTest extends PastellTestCase
{

    /** @var FastParapheur */
    private $fastParapheur;

    /**
     * @return DonneesFormulaire
     */
    private function getDefaultConnectorConfig(): DonneesFormulaire
    {
        $connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $connecteurConfig->setData('url', 'https://domain.tld');
        $connecteurConfig->setData('numero_abonnement', '1234');

        return $connecteurConfig;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $soapClient
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockSoapClientFactory(
        PHPUnit_Framework_MockObject_MockObject $soapClient
    ): PHPUnit_Framework_MockObject_MockObject {
        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);
        return $soapClientFactory;
    }

    /**
     * When the connection is ok
     *
     * @test
     * @throws Exception
     */
    public function whenConnectionIsOk()
    {
        $connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $connecteurConfig->setData('wsdl', 'https://domain.tld');
        $connecteurConfig->setData('numero_abonnement', '1234');

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('listRemainingAcknowledgements')
            ->willReturn(json_decode("", false));

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertEquals(json_decode("", false), $this->fastParapheur->testConnection());
    }

    /**
     * When the connection is not ok
     *
     * @test
     * @throws Exception
     */
    public function whenConnectionIsNotOk()
    {
        $this->expectException(Exception::class);
        //phpcs:disable
        $this->expectExceptionMessage("Erreur: l'abonne est inconnu ou l'utilisateur n'a pas les permissions pour y réaliser l'action demandée");
        //phpcs:enable

        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('listRemainingAcknowledgements')
            //phpcs:disable
            ->willThrowException(new Exception("Erreur: l'abonne est inconnu ou l'utilisateur n'a pas les permissions pour y réaliser l'action demandée"));
            //phpcs:enable

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->fastParapheur->testConnection();
    }

    /**
     * When getting subtypes
     *
     * @test
     */
    public function whenGettingSubtypes()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();
        $connecteurConfig->setData('circuits', 'CIRCUIT 1;CIRCUIT 2;PES;BUREAUTIQUE');

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $expectedData = [
            'CIRCUIT 1',
            'CIRCUIT 2',
            'PES',
            'BUREAUTIQUE'
        ];
        $this->assertSame($expectedData, $this->fastParapheur->getSousType());
    }

    /**
     * When sending a document
     *
     * @test
     * @throws Exception
     */
    public function whenSendingADocument()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('upload')
            ->willReturn(json_decode(json_encode([
                'return' => '1234-abcd'
            ])), true);

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertSame('1234-abcd', $this->fastParapheur->sendDocument("", "", "", "", ""));
    }

    /**
     * When sending a document and the server returns an error
     *
     * @test
     * @throws Exception
     */
    public function whenSendingADocumentWithAnUploadError()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('upload')
            ->willThrowException(new Exception("Fichier deja depose par un agent"));

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->sendDocument("", "", "", "", ""));

        $this->assertSame("Fichier deja depose par un agent", $this->fastParapheur->getLastError());
    }

    /**
     * When sending a document without error but without receiving its id on the parapheur
     *
     * @test
     * @throws Exception
     */
    public function whenSendingADocumentWithoutReceivingItsId()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('upload')
            ->willReturn(json_decode(json_encode([
                'return' => ''
            ])), true);

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->sendDocument("", "", "", "", ""));

        $this->assertSame(
            "Le parapheur n'a pas retourné d'identifiant de document : {\"return\":\"\"}",
            $this->fastParapheur->getLastError()
        );
    }

    /**
     * When sending a document with annexes
     *
     * @test
     * @throws Exception
     */
    public function whenSendingADocumentWithAnnexes()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('upload')
            ->willReturn(json_decode(json_encode([
                'return' => '1234-abcd'
            ])), true);

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);
        $this->fastParapheur->setTmpFolder($this->getObjectInstancier()->getInstance(TmpFolder::class));

        $this->assertSame(
            '1234-abcd',
            $this->fastParapheur->sendDocument(
                "empty.txt",
                "",
                __DIR__ . '/fixtures/empty.txt',
                "content of the main file",
                "",
                [
                    [
                        'file_path' => __DIR__ . '/fixtures/empty.txt',
                        'file_content' => 'content',
                        'name' => 'empty.txt'
                    ]
                ]
            )
        );
    }

    /**
     * When sending a document with annexes but the archive cannot be built
     *
     * @test
     * @throws Exception
     */
    public function whenTheArchiveCannotBeBuilt()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        $zipArchive = $this->getMockBuilder(ZipArchive::class)->getMock();
        $zipArchive
            ->expects($this->any())
            ->method('open')
            ->willReturn(false);

        /**
         * @var SoapClientFactory $soapClientFactory
         * @var ZipArchive $zipArchive
         */
        $this->fastParapheur = new FastParapheur(
            $soapClientFactory,
            $this->getObjectInstancier()->getInstance('TmpFolder'),
            $zipArchive
        );
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->sendDocument("", "", "", "", "", ['not empty']));
        $this->assertContains("Impossible de créer le fichier d'archive : ", $this->fastParapheur->getLastError());
    }

    /**
     * When getting the history of a document
     *
     * @test
     */
    public function whenGettingTheHistoryOfADocument()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('history')
            ->willReturn(json_decode(json_encode([
                'return' => [
                    [
                        'userFullName' => 'Agent',
                        'date' => '2019-04-03T14:46:49.274+01:00',
                        'stateName' => 'Préparé'
                    ]
                ]
            ])), true);

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $history = new stdClass();
        $history->userFullName = 'Agent';
        $history->date = '2019-04-03T14:46:49.274+01:00';
        $history->stateName = 'Préparé';
        $this->assertEquals([$history], $this->fastParapheur->getAllHistoriqueInfo('1234-abcd'));
    }

    /**
     * When we are fetching history of a document that doesn't exist
     *
     * @test
     */
    public function whenGettingHistoryOfADocumentThatDoesNotExist()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('history')
            ->willThrowException(new Exception("Le document n'existe pas"));

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->getAllHistoriqueInfo('1234-abcd'));
        $this->assertSame("Le document n'existe pas", $this->fastParapheur->getLastError());
    }

    /**
     * When we are not getting history for a document
     *
     * @test
     */
    public function whenNotGettingHistoryForADocument()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('history')
            ->willReturn(json_decode(json_encode([
                'return' => ''
            ])), true);

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->getAllHistoriqueInfo('1234-abcd'));
        $this->assertSame("L'historique du document n'a pas été trouvé", $this->fastParapheur->getLastError());
    }

    public function testLastHistorique()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $history = json_decode(json_encode(
            [
                [
                    'userFullName' => 'Agent',
                    'date' => '2019-04-03T14:46:49.274+01:00',
                    'stateName' => 'Préparé'
                ],
                [
                    'userFullName' => 'Agent',
                    'date' => '2019-04-03T15:35:03.449+01:00',
                    'stateName' => 'Classé'
                ]
            ]
        ));
        $this->assertSame(
            "03/04/2019 16:35:03 : [Classé]",
            $this->fastParapheur->getLastHistorique($history)
        );
    }

    /**
     * When getting the signed document
     *
     * @test
     */
    public function whenGettingTheSignedDocument()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('download')
            ->willReturn(json_decode(json_encode([
                'return' => [
                    'documentId' => '1234-abcd',
                    'content' => 'signed file content'
                ]
            ])), true);

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertEquals('signed file content', $this->fastParapheur->getSignature('1234-abcd'));
    }

    /**
     * When getting the signature of a document that doesn't exist
     *
     * @test
     */
    public function whenGettingSignatureOfADocumentThatDoesNotExist()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('download')
            ->willThrowException(new Exception("Le document n'existe pas"));

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->getSignature('1234-abcd'));
        $this->assertSame("Le document n'existe pas", $this->fastParapheur->getLastError());
    }

    /**
     * When the document can't be downloaded
     *
     * @test
     */
    public function whenTheDocumentCannotBeDownloaded()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('download')
            ->willReturn(json_decode(json_encode([
                'return' => [
                    'documentId' => '1234-abcd',
                    'content' => ''
                ]
            ])), true);

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->getSignature('1234-abcd'));
        $this->assertSame("Le document n'a pas pu être téléchargé", $this->fastParapheur->getLastError());
    }

    public function testMaxNumberDaysInParapheur()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();
        $connecteurConfig->setData('parapheur_nb_jour_max', 1234);

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertSame(
            1234,
            $this->fastParapheur->getNbJourMaxInConnecteur()
        );
    }

    public function testDefaultMaxNumberDaysInParapheur()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertSame(
            30,
            $this->fastParapheur->getNbJourMaxInConnecteur()
        );
    }

    /**
     * When sending an helios document
     *
     * @test
     * @throws Exception
     */
    public function whenSendingAnHeliosDocument()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('upload')
            ->willReturn(json_decode(json_encode([
                'return' => '1234-abcd'
            ])), true);

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertSame('1234-abcd', $this->fastParapheur->sendHeliosDocument("", "", "", "", "", ""));
    }

    /**
     * When sending an helios document and the server returns an error
     *
     * @test
     * @throws Exception
     */
    public function whenSendingAnHeliosDocumentWithAnError()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('upload')
            ->willThrowException(
                new Exception("Fichier refusé : un fichier PES avec le même nomfic a deja ete envoye")
            );

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->sendHeliosDocument("", "", "", "", "", ""));

        $this->assertSame(
            "Doublon | Fichier refusé : un fichier PES avec le même nomfic a deja ete envoye",
            $this->fastParapheur->getLastError()
        );
    }

    /**
     * When sending an helios document without error but without receiving its id on the parapheur
     *
     * @test
     * @throws Exception
     */
    public function whenSendingAnHeliosDocumentWithoutReceivingItsId()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('upload')
            ->willReturn(json_decode(json_encode([
                'return' => ''
            ])), true);

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->sendHeliosDocument("", "", "", "", "", ""));

        $this->assertSame(
            "Le parapheur n'a pas retourné d'identifiant de document : {\"return\":\"\"}",
            $this->fastParapheur->getLastError()
        );
    }

    /**
     * @test
     */
    public function whenDeletingARejectedFile()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->willReturnCallback(function ($soapMethod, $argument) {
                $this->assertSame('delete', $soapMethod);
                $this->assertSame(
                    [
                        [
                            'documentId' => '1234-abcd'
                        ]
                    ],
                    $argument
                );
                return [];
            });

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setLogger($this->getLogger());
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertSame(
            [],
            $this->fastParapheur->effacerDossierRejete('1234-abcd')
        );
    }

    /**
     * @test
     */
    public function whenDeletingARejectedFileException()
    {
        $connecteurConfig = $this->getDefaultConnectorConfig();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method('__call')
            ->with('delete')
            ->willThrowException(new Exception('exception message'));

        $soapClientFactory = $this->mockSoapClientFactory($soapClient);

        /** @var SoapClientFactory $soapClientFactory */
        $this->fastParapheur = new FastParapheur($soapClientFactory);
        $this->fastParapheur->setLogger($this->getLogger());
        $this->fastParapheur->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastParapheur->effacerDossierRejete(""));
        $this->assertSame(
            'exception message',
            $this->fastParapheur->getLastError()
        );
    }
}
