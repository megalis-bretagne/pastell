<?php

class IParapheurRecupHeliosTest extends PastellTestCase
{

    /**
     * @throws NotFoundException
     */
    public function testGetSignedPes()
    {
        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method('__call')
            ->will($this->returnCallback(
                function ($soapMethod, $arguments) {
                    if ($soapMethod === 'GetHistoDossier') {
                        $this->assertSame('another document id', $arguments[0]);

                        return json_decode(json_encode([
                            'LogDossier' => [
                                [
                                    'timestamp' => 1,
                                    'annotation' => 'annotation',
                                    'status' => 'status'

                                ]
                            ]
                        ]), false);
                    }
                    return json_decode('{"MessageRetour":{"codeRetour":"OK","message":"message.","severite":"INFO"}}', false);
                }
            ));

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        $this->getObjectInstancier()->setInstance(SoapClientFactory::class, $soapClientFactory);

        $connector = $this->createConnector('iParapheur', 'parapheur');
        $this->configureConnector($connector['id_ce'], [
            'iparapheur_activate' => true,
            'iparapheur_wsdl' => 'wsdl'
        ]);
        $this->associateFluxWithConnector($connector['id_ce'], 'helios-generique', 'signature');

        $document = $this->createDocument('helios-generique');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->setData('objet', 'PES document');
        $donneesFormulaire->addFileFromCopy(
            'fichier_pes',
            'pes.xml',
            __DIR__ . '/../fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml'
        );
        $donneesFormulaire->addFileFromCopy(
            'visuel_pdf',
            'visuel.pdf',
            __DIR__ . '/../../../fixtures/vide.pdf'
        );

        $this->triggerActionOnDocument($document['id_d'], 'send-iparapheur');
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');

        $donneesFormulaire->setData('iparapheur_dossier_id', 'another document id');

        $this->triggerActionOnDocument($document['id_d'], 'verif-iparapheur');

        /**
         * If this assertion fails with "La connexion avec le iParapheur a échoué : Failed asserting that two strings are identical."
         * It probably means that the assertion in the returnCallback() of the mocked soapClient is broken and the exception
         * is caught by the connector.
         */
        $this->assertLastMessage('01/01/1970 01:00:00 : [status] annotation');
    }
}