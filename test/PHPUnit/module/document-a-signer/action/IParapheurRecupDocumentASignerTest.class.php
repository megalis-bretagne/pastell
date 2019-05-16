<?php

class IParapheurRecupDocumentASignerTest extends PastellTestCase
{

    /**
     * @throws Exception
     */
    public function testVerifIparapheur()
    {
        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
            ->method("__call")
            ->will($this->returnCallback(
                function ($soapMethod, $arguments) {
                    if ($soapMethod === 'GetHistoDossier' && $arguments[0] === ' vide_pdf') {
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
        $this->associateFluxWithConnector($connector['id_ce'], 'document-a-signer', 'signature');

        $document = $this->createDocument('document-a-signer');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy(
            'document',
            'vide_signe.pdf',
            __DIR__ . "/../fixtures/vide.pdf"
        );

        $this->triggerActionOnDocument($document['id_d'], 'send-iparapheur');
        $this->assertLastMessage("Le document a été envoyé au parapheur électronique via le login  ");

        $donneesFormulaire->addFileFromCopy(
            'document_orignal',
            'vide.pdf',
            __DIR__ . "/../fixtures/vide.pdf"
        );

        $this->triggerActionOnDocument($document['id_d'], 'verif-iparapheur');
        $this->assertLastMessage('01/01/1970 01:00:00 : [status] annotation');

    }

}