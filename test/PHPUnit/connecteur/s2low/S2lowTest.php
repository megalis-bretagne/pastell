<?php

require_once( __DIR__.'/../../../../connecteur/s2low/S2low.class.php');


class S2lowTest extends PHPUnit\Framework\TestCase {

    private function getS2low($curl_response){
        $curlWrapper = $this->getMockBuilder('CurlWrapper')
            ->disableOriginalConstructor()
            ->getMock();

        $curlWrapper->expects($this->any())
            ->method('get')
            ->willReturn($curl_response);

        $curlWrapperFactory = $this->getMockBuilder('CurlWrapperFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $curlWrapperFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($curlWrapper);

        $objectInstancier = $this->getMockBuilder('ObjectInstancier')
            ->disableOriginalConstructor()
            ->getMock();

        $objectInstancier->expects($this->any())->method('getInstance')->willReturn($curlWrapperFactory);

        $collectiviteProperties = $this->getMockBuilder('DonneesFormulaire')
            ->disableOriginalConstructor()
            ->getMock();

        $collectiviteProperties->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($a){

                $result = array('user_login'=>'foo');
                if (isset($result[$a])) return $result[$a];
                return false;

            }));

        /** @var ObjectInstancier $objectInstancier */
        /** @var DonneesFormulaire $collectiviteProperties */
        $s2low = new S2low($objectInstancier);
        $s2low->setConnecteurConfig($collectiviteProperties);

        return $s2low;
    }

    /** @return DonneesFormulaire */
    protected function getDonneesFormulaire(){
        $donneesFormulaire =  $this->getMockBuilder('DonneesFormulaire')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var DonneesFormulaire $donneesFormulaire */
        return $donneesFormulaire;
    }

    public function testPostHeliosS2lowFailed(){
        $s2low = $this->getS2low("KO test");
        $this->expectExceptionMessage("La réponse de S²low n'a pas pu être analysée : KO test");
        $s2low->postHelios($this->getDonneesFormulaire());
    }

    public function testPostHeliosS2lowOK(){
        $s2low = $this->getS2low("<import><resultat>OK</resultat></import>");
        $this->assertTrue($s2low->postHelios($this->getDonneesFormulaire()));
    }

    public function testPostHeliosS2lowKO(){
        $s2low = $this->getS2low("<import><resultat>KO</resultat><message>foo</message></import>");
        $this->expectExceptionMessage("Erreur lors de l'envoi du PES : foo");
        $s2low->postHelios($this->getDonneesFormulaire());
    }
}