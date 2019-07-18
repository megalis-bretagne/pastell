<?php

require_once __DIR__.'/../../../../connecteur/iParapheur/IParapheur.class.php';
require_once PASTELL_PATH . DIRECTORY_SEPARATOR . 'pastell-core' . DIRECTORY_SEPARATOR . 'FileToSign.php';


class IParapheurTest extends PastellTestCase {

	const REPONSE_ARCHIVE_OK ='{"MessageRetour":{"codeRetour":"OK","message":"Dossier 201806111713 TESTA supprim\u00e9 du Parapheur.","severite":"INFO"}}';

	const REPONSE_ARCHIVE_KO ='{"MessageRetour":{"codeRetour":"KO","message":"Dossier 201806111713 TESTA introuvable.","severite":"ERROR"}}';



    /** @var  DonneesFormulaire */
    private $donneesFormulaire;

    protected function setUp() {
        parent::setUp();

        $this->donneesFormulaire = $this->getMockBuilder('DonneesFormulaire')
            ->disableOriginalConstructor()
            ->getMock();

        $this->donneesFormulaire->expects($this->any())
            ->method('get')
            ->willReturn("value");
    }


    private function getIParapheurConnecteur($soapClient = null){
		$soapClientFactory = $this->getMockBuilder('SoapClientFactory')->getMock();

		if (! $soapClient) {
			$soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();

			$soapClient->expects($this->any())
				->method("__call")
				->with(
					$this->equalTo('ArchiverDossier')
				)
				->willReturn($object = json_decode(self::REPONSE_ARCHIVE_OK, FALSE)
				);
		}

		$soapClientFactory->expects($this->any())
			->method('getInstance')
			->willReturn($soapClient);

		/** @var SoapClientFactory $soapClientFactory */
		/** @var DonneesFormulaire $donneesFormulaire */
		$iParapheur = new IParapheur($soapClientFactory);

		$collectiviteProperties = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$collectiviteProperties->setData('iparapheur_activate',true);
		$collectiviteProperties->setData('iparapheur_wsdl',"http://test");
		$collectiviteProperties->setData('iparapheur_type',"Actes");
		$iParapheur->setConnecteurConfig($collectiviteProperties);

		$iParapheur->setLogger($this->getLogger());
		return $iParapheur;
	}


    private function callWithMetadata($value){
        $connecteurProperties = $this->getMockBuilder('DonneesFormulaire')
            ->disableOriginalConstructor()
            ->getMock();

        $connecteurProperties->expects($this->any())
            ->method('get')
            ->willReturn($value);
        /** @var DonneesFormulaire $connecteurProperties */
		$iParapheur = $this->getIParapheurConnecteur();
        $iParapheur->setConnecteurConfig($connecteurProperties);

        $iParapheur->setSendingMetadata($this->donneesFormulaire);

        return $iParapheur->getSendingMetadata();
    }

    public function testMetaDataEmpty(){
        $this->assertEmpty($this->callWithMetadata(""));
    }

    public function testMetadataSimpleValue(){
        $this->assertEquals(array('bar'=>'value'),$this->callWithMetadata("foo:bar"));
    }

    public function testMetadataMultipleValue(){
        $this->assertEquals(array('bar'=>'value','buz'=>'value'),$this->callWithMetadata("foo:bar,baz:buz"));
    }

    public function testMetadataFailded(){
        $this->assertEmpty($this->callWithMetadata("foo"));
    }

    public function testMetadataFailed2(){
        $this->assertEmpty($this->callWithMetadata("foo,bar"));
    }

    public function testArchiver(){
		$iParapheur = $this->getIParapheurConnecteur();
    	$this->assertEquals(json_decode(self::REPONSE_ARCHIVE_OK),$iParapheur->archiver("foo"));
	}

	public function testArchiverKO(){
		$soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();

		$soapClient->expects($this->any())
			->method("__call")
			->with(
				$this->equalTo('ArchiverDossier')
			)
			->willReturn($object = json_decode(self::REPONSE_ARCHIVE_KO, FALSE)
			);
		$iParapheur = $this->getIParapheurConnecteur($soapClient);
		$this->assertFalse($iParapheur->archiver('foo'));
		$this->assertEquals(
			"Impossible d'archiver le dossier foo sur le i-Parapheur : ".self::REPONSE_ARCHIVE_KO,
			$iParapheur->getLastError()
		);
	}

	public function testArchiverFailed(){
		$soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();

		$soapClient->expects($this->any())
			->method("__call")
			->with(
				$this->equalTo('ArchiverDossier')
			)
			->willThrowException(new Exception("foo"));
		$iParapheur = $this->getIParapheurConnecteur($soapClient);
		$this->assertFalse($iParapheur->archiver('foo'));
		$this->assertEquals(
			"foo",
			$iParapheur->getLastError()
		);
	}


	public function sendDossierProvider() {

        $fileToSign = new FileToSign();
        $fileToSign->type = 'TYPE';
        $fileToSign->sousType = 'SOUS-TYPE';
        $fileToSign->dossierId = '1234-abcd';
        $fileToSign->document = new Fichier();
        $fileToSign->document->filename = 'nom fichier principal';
        $fileToSign->document->filepath = '/path/to/file';
        $fileToSign->document->content = 'file content';
        $fileToSign->document->contentType = 'application/pdf';
        $fileToSign->visualPdf = new Fichier();

        $fileToSign2 = new FileToSign();
        $fileToSign2->type = 'TYPE';
        $fileToSign2->sousType = 'SOUS-TYPE';
        $fileToSign2->dossierId = '1234-abcd';
        $fileToSign2->document = new Fichier();
        $fileToSign2->document->filename = 'nom fichier principal';
        $fileToSign2->document->filepath = '/path/to/file';
        $fileToSign2->document->content = file_get_contents(__DIR__.'/../../module/helios-generique/fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml');
        $fileToSign2->document->contentType = 'application/xml';
        $fileToSign2->visualPdf = new Fichier();
        $fileToSign2->visualPdf->content = 'visual pdf content';
        $fileToSign2->annexes = [];
        $annexe1 = new Fichier();
        $annexe1->filename = 'nom fichier principal';
        $annexe1->filepath = '/path/to/file';
        $annexe1->content = 'annexe 1 content';
        $annexe1->contentType = 'application/pdf';
        $fileToSign2->annexes[] = $annexe1;
        $fileToSign2->metadata = [
          'metadata_iparapheur' => 'value pastell',
          'metadata_iparapheur2' => 'value pastell2',
        ];

        return [
            [
                $fileToSign,
                [
                    'TypeTechnique' => 'TYPE',
                    'SousType' => 'SOUS-TYPE',
                    'DossierID' => '1234-abcd',
                    'DocumentPrincipal' => [
                        '_' => 'file content',
                        'contentType' => 'application/pdf'
                    ],
                    'Visibilite' => 'SERVICE',
                    'NomDocPrincipal' => 'nom fichier principal'
                ]
            ],
            [
                $fileToSign2,
                [
                    'TypeTechnique' => 'TYPE',
                    'SousType' => 'SOUS-TYPE',
                    'DossierID' => '1234-abcd',
                    'DocumentPrincipal' => [
                        '_' => file_get_contents(__DIR__.'/../../module/helios-generique/fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml'),
                        'contentType' => 'application/xml'
                    ],
                    'Visibilite' => 'SERVICE',
                    'NomDocPrincipal' => 'nom fichier principal',
                    'VisuelPDF' =>[
                        '_' => 'visual pdf content',
                        'contentType' => 'application/pdf'
                    ],
                    'XPathPourSignatureXML' => '//Bordereau',
                    'DocumentsAnnexes' => [
                        [
                            'nom' => 'nom fichier principal',
                            'fichier' => [
                                '_' => 'annexe 1 content',
                                'contentType' => 'application/pdf',
                            ],
                            'mimetype' => 'application/pdf',
                            'encoding' => 'UTF-8',
                        ]
                    ],
                    'MetaData' => [
                        'MetaDonnee' => [
                            [
                                'nom' => 'metadata_iparapheur',
                                'valeur' => 'value pastell',
                            ],
                            [
                                'nom' => 'metadata_iparapheur2',
                                'valeur' => 'value pastell2',
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider sendDossierProvider
     * @param FileToSign $fileToSign
     * @param array $expectedDataArray
     * @throws Exception
     */
    public function testSendDossier($fileToSign, $expectedDataArray)
    {
        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient->expects($this->any())
            ->method('__call')
            ->willReturnCallback(function ($soapMethod, $arguments) use ($expectedDataArray) {
                $this->assertSame([$expectedDataArray], $arguments);
                return json_decode(json_encode(['MessageRetour' => ['severite' => 'severite', 'message' => 'message', 'codeRetour' => 'OK']]));
            });
        $iParapheur = $this->getIParapheurConnecteur($soapClient);

        $this->assertSame('1234-abcd', $iParapheur->sendDossier($fileToSign));
    }

    public function testGestSousType(){
		$soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
		$soapClient->expects($this->any())
			->method('__call')
			->willReturnCallback(function ($soapMethod, $arguments)  {
				$this->assertSame('GetListeSousTypes',$soapMethod);
				return json_decode(json_encode(['SousType'=> ['BJ','Bordereau depense']]));
			});
		$iParapheur = $this->getIParapheurConnecteur($soapClient);

		$this->assertEquals(['BJ','Bordereau depense'],$iParapheur->getSousType());
	}

	public function testGestSousTypeFailed(){
		$soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
		$soapClient->expects($this->any())
			->method('__call')
			->willReturnCallback(function ($soapMethod, $arguments)  {
				$this->assertSame('GetListeSousTypes',$soapMethod);
				return new StdClass;
			});

		$iParapheur = $this->getIParapheurConnecteur($soapClient);
		$this->assertFalse($iParapheur->getSousType());
		$this->assertEquals("Aucun sous-type trouvé pour le type Actes",$iParapheur->getLastError());
	}

	/**
	 * @throws UnrecoverableException
	 */
	public function testSendDocumentTest(){
		$soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
		$soapClient->expects($this->any())
			->method('__call')
			->willReturnCallback(function ($soapMethod, $arguments)  {
				if ($soapMethod == "GetListeSousTypes"){
					return json_decode(json_encode(['SousType'=> ['Deliberation','document']]));
				}
				if ($soapMethod == "CreerDossier"){
					$this->assertStringEqualsFile(
						__DIR__."/../../../../connecteur/iParapheur/data-exemple/test-pastell-i-parapheur.pdf",
						$arguments[0]['DocumentPrincipal']['_']
					);
					$this->assertSame("Deliberation",$arguments[0]['SousType']);
					return json_decode(
						' {"MessageRetour":{"codeRetour":"OK","message":"Dossier XXX soumis dans le circuit","severite":"INFO"}}'
					);
				}

				throw new UnrecoverableException("Appel à la méthode $soapMethod inatendu");
			});
		$iParapheur = $this->getIParapheurConnecteur($soapClient);
		$this->assertNotEmpty(
			$iParapheur->sendDocumentTest()
		);
	}

}