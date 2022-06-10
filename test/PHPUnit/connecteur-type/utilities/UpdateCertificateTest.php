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
            '',
            __DIR__ . "/../../lib/fixtures/certificat.p12",
            0
        );

        $this->configureConnector($id_ce, [
            'certificat_password' => 'certificat'
        ]);

        $this->triggerActionOnConnector($id_ce, 'update-certificate');
        $connectorFiles = $this->getConnecteurFactory()->getConnecteurConfig($id_ce)->getAllFile();
        $this->assertContains('certificat_connexion', $connectorFiles);
        $this->assertContains('certificat_connexion_cert_pem', $connectorFiles);
        $this->assertContains('certificat_connexion_key_pem', $connectorFiles);
    }

    public function testDisplayGlobalUpdate()
    {
        $this->createConnector('s2low', 'Mon s2low connecteur entite');

        $id_ce = $this->createConnector('s2low', 's2low', 0)['id_ce'];

        $actionExecutorFactory = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);

        $this->expectOutputRegex("#Mon s2low connecteur entite#");
        $actionExecutorFactory->displayChoiceOnConnecteur(
            $id_ce,
            0,
            "mise-a-jour-certif-s2low",
            "changement-certificat"
        );
    }
}
