<?php

class PastellBootstrapTest extends PastellTestCase
{
    /** @var  PastellBootstrap */
    private $pastellBootstrap;

    protected function setUp()
    {
        parent::setUp();
        $this->pastellBootstrap = $this->getObjectInstancier()->getInstance(PastellBootstrap::class);
    }

    /**
     * @throws Exception
     */
    public function testTimestampCertificateAlreadyConfigured()
    {
        $this->pastellBootstrap->installHorodateur();
        $this->assertEquals("Le connecteur d'horodatage est configuré", $this->getLogRecords()[0]['message']);
    }

    /**
     * @throws Exception
     */
    public function testTimestampCertificate()
    {
        /** @var ConnecteurEntiteSQL $connecteurEntiteSQL */
        $connecteurEntiteSQL = $this->getObjectInstancier()->getInstance("ConnecteurEntiteSQL");
        $connecteurEntiteSQL->delete(10);
        $this->pastellBootstrap->installHorodateur();
        $this->assertEquals(
            "Horodateur interne installé et configuré avec un nouveau certificat autosigné",
            $this->getLogRecords()[3]['message']
        );
    }

    public function testInstallConnecteurFrequence()
    {

        $connecteurFrequenceSQL = $this->getObjectInstancier()->getInstance(ConnecteurFrequenceSQL::class);
        $connecteurFrequenceSQL->deleteAll();

        $this->pastellBootstrap->installConnecteurFrequenceDefault();
        $this->assertEquals(
            "Initialisation d'un connecteur `iparapheur` avec la fréquence `10`",
            $this->getLogRecords()[1]['message']
        );

        $result = $connecteurFrequenceSQL->getAll();

        $this->assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__ . "/fixtures/connecteur-frequence.json"),
            json_encode($result)
        );
    }

    /**
     * @throws Exception
     */
    public function testInstallPESViewerConnecteur()
    {
        $this->pastellBootstrap->installPESViewerConnecteur();
        $this->assertEquals(
            'pes-viewer',
            $this->getConnecteurFactory()
                ->getGlobalConnecteur('visionneuse_pes')
                ->getConnecteurInfo()['id_connecteur']
        );
    }

    /**
     * @throws Exception
     */
    public function testInstallPESViewerConnecteurWhenAlreadyInstalled()
    {
        $this->pastellBootstrap->installPESViewerConnecteur();
        $this->pastellBootstrap->installPESViewerConnecteur();
        $this->assertLastLog('Le connecteur de PES viewer est déjà configuré');
    }
}
