<?php

require_once( __DIR__.'/../../../../connecteur/iParapheur/IParapheur.class.php');


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
			"Impossible d'archive le dossier foo sur le i-Parapheur : ".self::REPONSE_ARCHIVE_KO,
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


}