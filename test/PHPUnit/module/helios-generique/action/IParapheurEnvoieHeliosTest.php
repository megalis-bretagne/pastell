<?php

class IParapheurEnvoieHeliosTest extends PastellTestCase
{
    /**
     * @throws NotFoundException
     */
    public function testSendPes()
    {
        $soapClient = $this->createMock(SoapClient::class);
        $soapClient
            ->method('__call')
            ->willReturnCallback(
                function ($soapMethod, $arguments) {
                    $this->assertArrayHasKey('VisuelPDF', $arguments[0]);
                    return json_decode('{"MessageRetour":{"codeRetour":"OK","message":"message.","severite":"INFO"}}', false);
                }
            );

        $soapClientFactory = $this->createMock(SoapClientFactory::class);
        $soapClientFactory
            ->method('getInstance')
            ->willReturn($soapClient);

        $this->getObjectInstancier()->setInstance(SoapClientFactory::class, $soapClientFactory);
        $connector = $this->createConnector('iParapheur', 'parapheur');
        $this->configureConnector($connector['id_ce'], [
            'iparapheur_wsdl' => 'wsdl',
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

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        $this->assertNotEmpty(
            $donneesFormulaire->get('iparapheur_dossier_id')
        );
    }
}
