<?php


class IParapheurRejetTest extends PastellTestCase
{
    use SoapUtilitiesTestTrait;

    public function testRejet()
    {
        $this->mockSoapClient(function ($soapMethod) {
            if ($soapMethod == 'GetHistoDossier') {
                return $this->returnSoapResponseFromXMLFile(
                    __DIR__ . "/fixtures/iparapheur-histo-dossier-rejetCachet.xml"
                );
            }
            if ($soapMethod == 'GetDossier') {
                return json_decode(json_encode([
                    'DocPrincipal' => [
                        '_' => '%PDF1-4',
                        'contentType' => 'application/pdf'
                    ],
                    'NomDocPrincipal' => 'test éàê accent.pdf',
                    'MessageRetour' => [
                        'codeRetour' => 'OK'
                    ]
                ]), false);
            }
            if ($soapMethod == 'EffacerDossierRejete') {
                return json_decode(
                    '{"MessageRetour":{"codeRetour":"OK","message":"message.","severite":"INFO"}}',
                    false
                );
            }
            throw new UnrecoverableException("unknow $soapMethod call");
        });

        $id_ce = $this->createConnector('iParapheur', "i-parapheur")['id_ce'];

        $this->configureConnector($id_ce, [
            'iparapheur_wsdl' => 'https://foo',
        ]);

        $this->associateFluxWithConnector($id_ce, 'pdf-generique', 'signature');

        $id_d = $this->createDocument('pdf-generique')['id_d'];

        $this->triggerActionOnDocument($id_d, 'verif-iparapheur');

        $this->assertLastDocumentAction('rejet-iparapheur', $id_d);
    }
}
