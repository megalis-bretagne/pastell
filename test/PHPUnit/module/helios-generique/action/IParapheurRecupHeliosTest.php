<?php

use PHPUnit\Framework\MockObject\MockObject;

class IParapheurRecupHeliosTest extends PastellTestCase
{
    /**
     * @param MockObject $soapClient
     */
    private function mockSoapClientFactory(MockObject $soapClient): void
    {
        $soapClientFactory = $this->createMock(SoapClientFactory::class);
        $soapClientFactory
            ->method('getInstance')
            ->willReturn($soapClient);

        $this->getObjectInstancier()->setInstance(SoapClientFactory::class, $soapClientFactory);
    }

    private function createAndAssociateIparapheurConnector(): void
    {
        $connector = $this->createConnector('iParapheur', 'parapheur');
        $this->configureConnector($connector['id_ce'], [
            'iparapheur_wsdl' => 'wsdl'
        ]);
        $this->associateFluxWithConnector($connector['id_ce'], 'helios-generique', 'signature');
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return DonneesFormulaire
     */
    private function setDefaultDataToDocument(DonneesFormulaire $donneesFormulaire): DonneesFormulaire
    {
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

        return $donneesFormulaire;
    }

    /**
     * @throws NotFoundException
     */
    public function testWhenModifyingDocumentIdAfterBeingSent()
    {
        $soapClient = $this->createMock(SoapClient::class);
        $soapClient
            ->method('__call')
            ->willReturnCallback(function ($soapMethod, $arguments) {
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
            });

        $this->mockSoapClientFactory($soapClient);

        $this->createAndAssociateIparapheurConnector();

        $document = $this->createDocument('helios-generique');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire = $this->setDefaultDataToDocument($donneesFormulaire);

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

    /**
     * @throws NotFoundException
     */
    public function testThatDocumentIdIsUsedWhenRequestingItOnParapheur()
    {
        $this->createAndAssociateIparapheurConnector();
        $document = $this->createDocument('helios-generique');
        $id_d = $document['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->setDefaultDataToDocument($donneesFormulaire);

        $soapClient = $this->createMock(SoapClient::class);
        $soapClient
            ->method('__call')
            ->willReturnCallback(
                function ($soapMethod, $arguments) use ($id_d) {
                    if (in_array($soapMethod, ['GetHistoDossier', 'GetDossier'])) {
                        $this->assertSame(
                            $this->getDonneesFormulaireFactory()->get($id_d)->get('iparapheur_dossier_id'),
                            $arguments[0]
                        );
                    }

                    if ($soapMethod === 'GetHistoDossier') {
                        return json_decode(json_encode([
                            'LogDossier' => [
                                [
                                    'timestamp' => 1,
                                    'annotation' => 'annotation',
                                    'status' => 'Archive'

                                ]
                            ]
                        ]), false);
                    }
                    return json_decode('{"MessageRetour":{"codeRetour":"OK","message":"message.","severite":"INFO"}}', false);
                }
            );

        $this->mockSoapClientFactory($soapClient);

        $this->triggerActionOnDocument($document['id_d'], 'send-iparapheur');
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');
        $this->triggerActionOnDocument($document['id_d'], 'verif-iparapheur');

        /**
         * If this assertion fails with "La connexion avec le iParapheur a échoué : Failed asserting that two strings are identical."
         * It probably means that the assertion in the returnCallback() of the mocked soapClient is broken and the exception
         * is caught by the connector.
         */
        $this->assertLastMessage('La signature a été récupérée');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $filename = substr($donneesFormulaire->getFileName('fichier_pes'), 0, -4);
        $filename_signe = $filename . "_signe.xml";
        $this->assertEquals($donneesFormulaire->getFileName('fichier_pes_signe'), $filename_signe);
        $this->assertNotEquals($donneesFormulaire->getFileName('fichier_pes'), $donneesFormulaire->getFileName('fichier_pes_signe'));
    }
}
