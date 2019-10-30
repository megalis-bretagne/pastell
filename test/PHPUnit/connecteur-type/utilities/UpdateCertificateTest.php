<?php

class UpdateCertificateTest extends PastellTestCase
{

    /**
     * @throws Exception
     */
    public function testCertificate()
    {
        $connector = $this->createConnector('fast-parapheur', 'Fast Parapheur');
        $id_ce = $connector['id_ce'];
        $connectorConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce);

        $connectorConfig->addFileFromCopy(
            'certificat_connexion',
            'test1234.p12',
            __DIR__ . "/../fixtures/test1234.p12",
            0
        );

        $this->configureConnector($id_ce, [
            'certificat_password' => 'test1234'
        ]);

        $this->triggerActionOnConnector($id_ce, 'update-certificate');

        $connectorFiles = $this->getConnecteurFactory()->getConnecteurConfig($id_ce)->getAllFile();

        $this->assertContains('certificat_connexion', $connectorFiles);
        $this->assertContains('certificat_connexion_cert_pem', $connectorFiles);
        $this->assertContains('certificat_connexion_key_pem', $connectorFiles);
    }
}
