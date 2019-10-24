<?php

use Sabre\HTTP\ClientHttpException;

require_once __DIR__ . "/../../../../connecteur/fast-tdt/FastTdt.class.php";
require_once __DIR__ . "/../../../../connecteur/fast-tdt/FastTdtException.class.php";

class FastTdtTest extends PastellTestCase
{

    /** @var FastTdt */
    private $fastTdt;

    /**
     * @return DonneesFormulaire
     */
    private function getDefaultConnecteurConfig(): DonneesFormulaire
    {
        $connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $connecteurConfig->setData('url', 'https://domain.tld');
        $connecteurConfig->setData('departement', '999');
        $connecteurConfig->setData('numero_abonnement', '1234');
        $connecteurConfig->setData('prefixe_editeur', 'abcd');
        return $connecteurConfig;
    }

    /**
     * @param int $numberOfAnnexes
     * @return DonneesFormulaire
     */
    private function getDefaultActeDonneesFormulaire($numberOfAnnexes): DonneesFormulaire
    {
        $acteDonneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $acteDonneesFormulaire->setData('acte_nature', '3');
        $acteDonneesFormulaire->setData('numero_de_lacte', '201905151412');
        $acteDonneesFormulaire->setData('objet', 'This is a test');
        $acteDonneesFormulaire->setData('date_de_lacte', '2019-05-15');
        $acteDonneesFormulaire->setData('document_papier', '');
        $acteDonneesFormulaire->setData('classification', '1.1 Marchés publics');
        $acteDonneesFormulaire->setData('type_acte', '99_AI');
        $acteDonneesFormulaire->addFileFromCopy('arrete', 'arrete.pdf', __DIR__ . '/fixtures/vide.pdf');
        for ($i = 0; $i < $numberOfAnnexes; ++$i) {
            $acteDonneesFormulaire->addFileFromCopy(
                'autre_document_attache',
                "$i.pdf",
                __DIR__ . '/fixtures/vide.pdf',
                $i
            );
        }
        return $acteDonneesFormulaire;
    }

    /**
     * When building the webdav url
     *
     * @test
     */
    public function whenBuildingTheWebdavUrl()
    {
        $connecteurConfig = $this->getDefaultConnecteurConfig();

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();


        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->assertEquals('https://domain.tld/webdav/1234/abcd/', $this->fastTdt->getWebdavUrl());
    }

    public function whenTestingConnectionProvider()
    {
        return [
            ['https://domain.tld'],
            ['https://domain.tld?wsdl']
        ];
    }

    /**
     * When the connection is ok
     *
     * @dataProvider whenTestingConnectionProvider
     * @test
     * @param $url
     * @throws Exception
     */
    public function whenConnectionIsOk($url)
    {
        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $webdavWrapper->expects($this->any())->method('isConnected')->willReturn(true);

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('listRemainingAcknowledgements')
            ->willReturn(true);

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $connecteurConfig->setData('url', $url);
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->assertTrue($this->fastTdt->testConnexion());
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
        $this->expectExceptionMessage("Le serveur ne présente pas le header Dav");

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $webdavWrapper
            ->expects($this->any())
            ->method('isConnected')
            ->willThrowException(new Exception("Le serveur ne présente pas le header Dav"));
        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());

        $this->fastTdt->testConnexion();
    }

    /**
     * @throws Exception
     */
    public function testGetClassification()
    {
        $connecteurConfig = $this->getDefaultConnecteurConfig();

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $webdavWrapper
            ->expects($this->any())
            ->method('listFolder')
            ->willReturn([
                '999-1234----7-2_1.xml',
                '999-1234----7-2_7.xml',
                '999-1234----7-2_9.xml',
            ]);

        $webdavWrapper
            ->expects($this->exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                file_get_contents(__DIR__ . '/fixtures/999-1234----7-2_1.xml'),
                file_get_contents(__DIR__ . '/fixtures/999-1234----7-2_7.xml'),
                file_get_contents(__DIR__ . '/fixtures/999-1234----7-2_9.xml')
            );
        $webdavWrapper
            ->expects($this->exactly(2))
            ->method('delete')
            ->willReturn(['statusCode' => 204]);

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->assertSame(
            file_get_contents(__DIR__ . '/fixtures/999-1234----7-2_1.xml'),
            $this->fastTdt->getClassification()
        );
    }

    /**
     * When removing classification files from the server
     *
     * @test
     * @throws Exception
     */
    public function whenPurgingTheClassificationFiles()
    {
        $connecteurConfig = $this->getDefaultConnecteurConfig();

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $webdavWrapper
            ->expects($this->any())
            ->method('listFolder')
            ->willReturn([
                '999-1234----7-2_1.xml',
                '999-1234----7-2_7.xml',
                '999-1234----7-2_9.xml',
            ]);

        $webdavWrapper
            ->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive(
                ['', '999-1234----7-2_1.xml'],
                ['', '999-1234----7-2_9.xml']
            )
            ->willReturn(['statusCode' => 204]);

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->assertTrue($this->fastTdt->purgeClassificationFiles([
            '999-1234----7-2_1.xml',
            '999-1234----7-2_9.xml',
        ]));
    }

    /**
     * @test
     * @throws Exception
     */
    public function whenTryingToPurgeTheClassificationFiles()
    {
        $this->expectException(FastTdtException::class);
        //phpcs:disable
        $this->expectExceptionMessage("Impossible de supprimer le fichier de classification 999-1234----7-2_1.xml : Code : 403 Forbidden");
        //phpcs:enable

        $connecteurConfig = $this->getDefaultConnecteurConfig();

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $webdavWrapper
            ->expects($this->any())
            ->method('listFolder')
            ->willReturn([
                '999-1234----7-2_1.xml',
                '999-1234----7-2_7.xml',
                '999-1234----7-2_9.xml',
            ]);

        $webdavWrapper
            ->expects($this->exactly(1))
            ->method('delete')
            ->willReturn(
                [
                    'statusCode' => 403,
                    'body' => 'Forbidden'
                ]
            );

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->fastTdt->purgeClassificationFiles([
            '999-1234----7-2_1.xml',
            '999-1234----7-2_9.xml',
        ]);
    }

    public function whenSendingAnActProvider()
    {
        return [
            [0, ''],
            [1, '["22_CO"]'],
            [10, '']
        ];
    }

    /**
     * When successfully sending an act
     *
     * @dataProvider whenSendingAnActProvider
     * @param $numberOfAnnexes
     * @param $typePj
     * @test
     * @throws Exception
     */
    public function whenSendingAnAct($numberOfAnnexes, $typePj)
    {
        $connecteurConfig = $this->getDefaultConnecteurConfig();
        $connecteurConfig->addFileFromCopy(
            'classification_file',
            'classification.xml',
            __DIR__ . '/fixtures/999-1234----7-2_1.xml'
        );
        $connecteurConfig->setData('classification_date', '2019-04-18');

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        // We just want addDocument() not to throw an error
        $webdavWrapper
            ->expects($this->any())
            ->method('addDocument')
            ->willReturn([]);

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('traiterACTES')
            ->willReturn(json_decode(json_encode(['code' => '0'])));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $acteDonneesFormulaire = $this->getDefaultActeDonneesFormulaire($numberOfAnnexes);
        $acteDonneesFormulaire->setData('type_pj', $typePj);

        $this->assertTrue($this->fastTdt->postActes($acteDonneesFormulaire));
        $this->assertSame('999-1234-20190515-201905151412-AI', $acteDonneesFormulaire->get('tedetis_transaction_id'));
    }

    /**
     * When sending an act with an error
     *
     * @test
     * @throws Exception
     */
    public function whenSendingAnActWithAnError()
    {
        $this->expectException(FastTdtException::class);
        $this->expectExceptionMessage("Erreur lors du traitement de l'acte : 1x2 : Enveloppe mal formée");
        $connecteurConfig = $this->getDefaultConnecteurConfig();
        $connecteurConfig->addFileFromCopy(
            'classification_file',
            'classification.xml',
            __DIR__ . '/fixtures/999-1234----7-2_1.xml'
        );
        $connecteurConfig->setData('classification_date', '2019-04-18');

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $webdavWrapper
            ->expects($this->any())
            ->method('exists')
            ->willReturn(true);
        $webdavWrapper
            ->expects($this->any())
            ->method('delete')
            ->willReturn([]);
        $webdavWrapper
            ->expects($this->any())
            ->method('addDocument')
            ->willReturn([]);

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('traiterACTES')
            ->willReturn(json_decode(json_encode([
                'code' => '1x2',
                'detail' => 'Enveloppe mal formée'
            ])));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $acteDonneesFormulaire = $this->getDefaultActeDonneesFormulaire(5);
        $acteDonneesFormulaire->setData('type_pj', '');

        $this->fastTdt->postActes($acteDonneesFormulaire);
    }

    public function whenGettingStatusProvider()
    {
        return [
            [
                [
                    '999-1234-20190515-201905151412-AI-1-2_0.xml' => []
                ],
                file_get_contents(__DIR__ . '/fixtures/999-1234----1-2_0.xml'),
                TdtConnecteur::STATUS_ACQUITTEMENT_RECU
            ],
            [
                [],
                '',
                TdtConnecteur::STATUS_TRANSMIS
            ],
            [
                [
                    '999-1234-20190515-201905151412-AI-1-3_0.xml' => []
                ],
                file_get_contents(__DIR__ . '/fixtures/999-1234----1-3_0.xml'),
                TdtConnecteur::STATUS_ERREUR
            ],
            [
                [
                    '999-1234-20190709-201905151412-AI-6-2_0.xml' => []
                ],
                file_get_contents(__DIR__ . '/fixtures/999-1234----6-2_0.xml'),
                TdtConnecteur::STATUS_ACQUITTEMENT_RECU
            ],

        ];
    }

    /**
     * @dataProvider whenGettingStatusProvider
     * @test
     * @param $expectedFilesInDirectory
     * @param $tdtReturnXmlContent
     * @param $expectedStatus
     * @throws ClientHttpException
     */
    public function whenGettingStatus($expectedFilesInDirectory, $tdtReturnXmlContent, $expectedStatus)
    {
        $connecteurConfig = $this->getDefaultConnecteurConfig();
        $connecteurConfig->addFileFromCopy(
            'classification_file',
            'classification.xml',
            __DIR__ . '/fixtures/999-1234----7-2_1.xml'
        );
        $connecteurConfig->setData('classification_date', '2019-04-18');

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $webdavWrapper
            ->expects($this->any())
            ->method('propfind')
            ->willReturn($expectedFilesInDirectory);
        $webdavWrapper
            ->expects($this->any())
            ->method('get')
            ->willReturn($tdtReturnXmlContent);
        $webdavWrapper
            ->expects($this->any())
            ->method('delete')
            ->willReturn([]);

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $this->fastTdt->setConnecteurConfig($connecteurConfig);
        $this->fastTdt->setDocDonneesFormulaire($this->getDefaultActeDonneesFormulaire(1));

        $this->assertEquals(
            $expectedStatus,
            $this->fastTdt->getStatus('999-1234-20190515-201905151412-AI')
        );
    }

    /**
     * @test
     * @throws ClientHttpException
     */
    public function whenGettingStatusWithAnError()
    {
        $connecteurConfig = $this->getDefaultConnecteurConfig();
        $connecteurConfig->addFileFromCopy(
            'classification_file',
            'classification.xml',
            __DIR__ . '/fixtures/999-1234----7-2_1.xml'
        );
        $connecteurConfig->setData('classification_date', '2019-04-18');

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $webdavWrapper
            ->expects($this->any())
            ->method('propfind')
            ->willReturn(
                [
                    '999-1234-20190515-201905151412-AI-1-2_0.xml' => [
                        '{DAV:}getlastmodified' => 'Thu, 23 May 2019 09:48:18 GMT',
                        '{DAV:}getcontentlength' => '988',
                        '{DAV:}getcontenttype' => 'application/xml'
                    ],
                    '999-1234-20190515-201905151412-AI-1-2_1.xml' => [
                        '{DAV:}getlastmodified' => 'Thu, 24 May 2019 09:48:18 GMT',
                        '{DAV:}getcontentlength' => '988',
                        '{DAV:}getcontenttype' => 'application/xml'
                    ],
                ]
            );
        $webdavWrapper
            ->expects($this->any())
            ->method('get')
            ->willReturn(file_get_contents(__DIR__ . '/fixtures/999-1234----1-3_0.xml'));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $this->fastTdt->setConnecteurConfig($connecteurConfig);
        $docDonneesFormulaire = $this->getDefaultActeDonneesFormulaire(1);
        $docDonneesFormulaire->id_d = 'abcd1234';
        $this->fastTdt->setDocDonneesFormulaire($docDonneesFormulaire);

        $this->assertEquals(
            TdtConnecteur::STATUS_ERREUR,
            $this->fastTdt->getStatus('999-1234-20190515-201905151412-AI')
        );
        $this->assertSame('2', $this->getJournal()->getNbLine());
        $this->assertSame(
            json_encode([
                'filename' => '999-1234-20190515-201905151412-AI-1-2_0.xml',
                'mtime' => 'Thu, 23 May 2019 09:48:18 GMT',
                'content_length' => '988',
                'content_type' => 'application/xml',
                'md5sum' => '636fa5f18a515c6d1ed2d203c2bd2809'
            ]),
            $this->getJournal()->getInfo(1)['message']
        );
        $this->assertSame(
            json_encode([
                'filename' => '999-1234-20190515-201905151412-AI-1-2_1.xml',
                'mtime' => 'Thu, 24 May 2019 09:48:18 GMT',
                'content_length' => '988',
                'content_type' => 'application/xml',
                'md5sum' => '636fa5f18a515c6d1ed2d203c2bd2809'
            ]),
            $this->getJournal()->getInfo(2)['message']
        );
    }

    /**
     * @test
     * @throws DocapostParapheurSoapClientException
     */
    public function whenSendingAPes()
    {
        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('upload')
            ->willReturn(json_decode(json_encode(['return' => '1234abcd'])));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $connecteurConfig = $this->getDefaultConnecteurConfig();
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $heliosDonneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $heliosDonneesFormulaire->setData('tedetis_transaction_id', 'EMPTY_NOW');
        $heliosDonneesFormulaire->addFileFromCopy(
            'fichier_pes_signe',
            'empty.pdf',
            __DIR__ . '/fixtures/vide.pdf'
        );
        $this->fastTdt->setDocDonneesFormulaire($heliosDonneesFormulaire);

        $this->fastTdt->postHelios($heliosDonneesFormulaire);

        $this->assertSame(
            '1234abcd',
            $this->fastTdt->getDocDonneesFormulaire()->get('tedetis_transaction_id')
        );
    }

    /**
     * @test
     * @throws DocapostParapheurSoapClientException
     */
    public function whenDownloadingAcknowledgment()
    {
        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('downloadAcknowledgement')
            ->willReturn(json_decode(json_encode(['return' => ['content' => 'pes acquit']])));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $connecteurConfig = $this->getDefaultConnecteurConfig();
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->assertSame(
            'pes acquit',
            $this->fastTdt->getFichierRetour('1234abcd')
        );
    }

    /**
     * @test
     * @throws DocapostParapheurSoapClientException
     */
    public function whenDownloadingAcknowledgmentException()
    {
        $this->expectException(DocapostParapheurSoapClientException::class);
        $this->expectExceptionMessage("Le PES Acquit n'a pas pu être téléchargé");

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('downloadAcknowledgement')
            ->willReturn(json_decode(json_encode(['return' => 'ddd'])));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $connecteurConfig = $this->getDefaultConnecteurConfig();
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->fastTdt->getFichierRetour('1234abcd');
    }


    public function whenGettingHeliosStatusProvider()
    {
        return [
            [
                [
                    'return' => [
                        [
                            'userFullName' => '',
                            'date' => '',
                            'stateName' => 'Préparé'
                        ]
                    ]
                ],
                TdtConnecteur::STATUS_HELIOS_TRAITEMENT
            ],
            [
                [
                    'return' => [
                        [
                            'userFullName' => '',
                            'date' => '',
                            'stateName' => 'Échec du traitement FAST'
                        ]
                    ]
                ],
                TdtConnecteur::STATUS_ERREUR
            ],
            [
                [
                    'return' => [
                        [
                            'userFullName' => '',
                            'date' => '',
                            'stateName' => 'Acquittement Hélios'
                        ]
                    ]
                ],
                TdtConnecteur::STATUS_HELIOS_INFO
            ],
        ];
    }

    /**
     * @dataProvider whenGettingHeliosStatusProvider
     * @test
     * @param $history
     * @param $expectedStatus
     * @throws DocapostParapheurSoapClientException
     */
    public function whenGettingHeliosStatus($history, $expectedStatus)
    {
        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->will($this->returnCallback(
                function ($soapMethod) use ($history) {
                    if ($soapMethod === 'listRemainingAcknowledgements') {
                        return json_decode(json_encode(['return' => []]));
                    }
                    return json_decode(json_encode($history));
                }
            ));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $connecteurConfig = $this->getDefaultConnecteurConfig();
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->assertSame(
            $expectedStatus,
            $this->fastTdt->getStatusHelios('1234abcd')
        );
    }

    /**
     * @test
     * @throws DocapostParapheurSoapClientException
     */
    public function whenGettingHeliosStatusException()
    {
        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('listRemainingAcknowledgements')
            ->willThrowException(new Exception('exception message'));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $connecteurConfig = $this->getDefaultConnecteurConfig();
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->assertFalse($this->fastTdt->getStatusHelios('1234abcd'));

        $this->assertSame(
            'exception message',
            $this->fastTdt->getLastError()
        );
    }


    /**
     * @test
     * @throws ClientHttpException
     * @throws FastTdtException
     */
    public function whenCancellingAnAct()
    {
        $connecteurConfig = $this->getDefaultConnecteurConfig();

        $webdavWrapper = $this->getMockBuilder(WebdavWrapper::class)->getMock();
        $webdavWrapper
            ->expects($this->any())
            ->method('addDocument')
            ->willReturn([]);

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->with('traiterACTES')
            ->willReturn(json_decode(json_encode(['code' => '0'])));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        /**
         * @var WebdavWrapper $webdavWrapper
         * @var SoapClientFactory $soapClientFactory
         */
        $this->fastTdt = new FastTdt($webdavWrapper, $soapClientFactory, $this->getJournal());
        $this->fastTdt->setConnecteurConfig($connecteurConfig);

        $this->assertSame(
            '999-1234-20190515-201905151412-AI',
            $this->fastTdt->annulationActes('999-1234-20190515-201905151412-AI')
        );
    }
}
