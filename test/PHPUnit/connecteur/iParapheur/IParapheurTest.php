<?php

require_once( __DIR__.'/../../../../connecteur/iParapheur/IParapheur.class.php');


class IParapheurTest extends PastellTestCase {

    /** @var  IParapheur */
    private $iParapheur;

    /** @var  DonneesFormulaire */
    private $donneesFormulaire;

    protected function setUp() {
        parent::setUp();
        /** @var SoapClientFactory $soapClientFactory */
        $soapClientFactory = $this->getMockBuilder('SoapClientFactory')->getMock();


        $this->donneesFormulaire = $this->getMockBuilder('DonneesFormulaire')
            ->disableOriginalConstructor()
            ->getMock();

        $this->donneesFormulaire->expects($this->any())
            ->method('get')
            ->willReturn("value");

        /** @var DonneesFormulaire $donneesFormulaire */
        $this->iParapheur = new IParapheur($soapClientFactory);

    }

    private function callWithMetadata($value){
        $connecteurProperties = $this->getMockBuilder('DonneesFormulaire')
            ->disableOriginalConstructor()
            ->getMock();

        $connecteurProperties->expects($this->any())
            ->method('get')
            ->willReturn($value);
        /** @var DonneesFormulaire $connecteurProperties */

        $this->iParapheur->setConnecteurConfig($connecteurProperties);

        $this->iParapheur->setSendingMetadata($this->donneesFormulaire);

        return $this->iParapheur->getSendingMetadata();
    }

    public function testMetaDataEmpty(){
        $this->iParapheur->setSendingMetadata($this->donneesFormulaire);
        $this->assertEmpty($this->callWithMetadata(""));
    }

    public function testMetadataSimpleValue(){
        $this->iParapheur->setSendingMetadata($this->donneesFormulaire);
        $this->assertEquals(array('bar'=>'value'),$this->callWithMetadata("foo:bar"));
    }

    public function testMetadataMultipleValue(){
        $this->iParapheur->setSendingMetadata($this->donneesFormulaire);
        $this->assertEquals(array('bar'=>'value','buz'=>'value'),$this->callWithMetadata("foo:bar,baz:buz"));
    }

    public function testMetadataFailded(){
        $this->iParapheur->setSendingMetadata($this->donneesFormulaire);
        $this->assertEmpty($this->callWithMetadata("foo"));
    }

    public function testMetadataFailed2(){
        $this->iParapheur->setSendingMetadata($this->donneesFormulaire);
        $this->assertEmpty($this->callWithMetadata("foo,bar"));
    }


}