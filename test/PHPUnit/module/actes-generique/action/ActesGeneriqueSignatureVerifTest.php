<?php

class ActesGeneriqueSignatureVerifTest extends PastellTestCase
{

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testActeDateShouldBeEditableAfterDocumentIsSigned()
    {
        $connector = $this->createConnector('iParapheur', 'parapheur');
        $this->configureConnector($connector['id_ce'], [
            'iparapheur_activate' => true,
            'iparapheur_wsdl' => 'wsdl'
        ]);
        $this->associateFluxWithConnector($connector['id_ce'], 'actes-generique', 'signature');

        $document = $this->createDocument('actes-generique');
        $id_d = $document['id_d'];

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();
        $soapClient
            ->expects($this->any())
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

        $soapClientFactory = $this->getMockBuilder(SoapClientFactory::class)->getMock();
        $soapClientFactory
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        $this->getObjectInstancier()->setInstance(SoapClientFactory::class, $soapClientFactory);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromData('arrete', 'arrete.pdf', 'content');
        $this->getInternalAPI()->patch('/entite/1/document/' . $id_d, [
            'acte_nature' => '1',
            'numero_de_lacte' => '20190718',
            'objet' => 'objet',
            'date_de_lacte' => '2019-07-18',
            'classification' => '1.1',
            'envoi_signature' => true,
            'envoi_tdt' => true,
            'iparapheur_type' => 'TYPE',
            'iparapheur_sous_type' => 'SOUS_TYPE'
        ]);

        $this->triggerActionOnDocument($id_d, 'send-iparapheur');
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');
        $this->triggerActionOnDocument($id_d, 'verif-iparapheur');
        $this->assertLastMessage('La signature a été récupérée');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $this->assertTrue($donneesFormulaire->isEditable('date_de_lacte'));
    }
}