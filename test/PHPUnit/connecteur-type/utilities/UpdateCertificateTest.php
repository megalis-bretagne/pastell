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

    public function testDisplayGlobalUpdate()
    {
        $this->createConnector('iParapheur', 'Mon i-Parapheur connecteur entite');

        $id_ce = $this->createConnector('iParapheur', 'i-Parapheur', 0)['id_ce'];

        $actionExecutorFactory = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);

        $this->expectOutputRegex("#Mon i-Parapheur connecteur entite#");
        $actionExecutorFactory->displayChoiceOnConnecteur(
            $id_ce,
            0,
            "mise-a-jour-certif-i-parapheur",
            "changement-certificat"
        );
    }
}
