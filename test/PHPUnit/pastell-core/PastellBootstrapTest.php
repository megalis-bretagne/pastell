<?php

class PastellBootstrapTest extends PastellTestCase {

    /** @var  PastellBootstrap */
    private $pastellBootstrap;

    protected function setUp() {
        parent::setUp();
        $this->pastellBootstrap = $this->getObjectInstancier()->getInstance('PastellBootstrap');
    }

    public function testTimestampCertificateAlreadyConfigured(){
        $this->expectOutputRegex("#Le connecteur d'horodatage est configuré#");
        $this->pastellBootstrap->installHorodateur();
    }

    public function testTimestampCertificate(){
        /** @var ConnecteurEntiteSQL $connecteurEntiteSQL */
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance("ConnecteurEntiteSQL");
        $connecteurEntiteSQL->delete(10);
        $this->expectOutputRegex(
            "#Horodateur interne installé et configuré avec un nouveau certificat autosigné#"
        );
        $this->pastellBootstrap->installHorodateur();
    }

}