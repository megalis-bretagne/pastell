<?php

class IParapheurDateSignatureTest extends PastellTestCase
{
    use SoapUtilitiesTestTrait;

    public function testSigne()
    {
        $this->mockSoapClient(function ($soapMethod) {
            if ($soapMethod === 'CreerDossier') {
                return json_decode(
                    '{"MessageRetour":{"codeRetour":"OK","message":"","severite":"INFO"}}'
                );
            }
            if ($soapMethod == 'GetHistoDossier') {
                return $this->returnSoapResponseFromXMLFile(
                    __DIR__ . "/fixtures/iparapheur-GetHistoDossier-signe.xml"
                );
            }
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
        });

        $id_ce = $this->createConnector('iParapheur', "i-parapheur")['id_ce'];
        $this->configureConnector($id_ce, [
            'iparapheur_wsdl' => 'https://foo',
        ]);

        $this->associateFluxWithConnector($id_ce, 'ls-document-pdf', 'signature');

        $id_d = $this->createDocument('ls-document-pdf')['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            'iparapheur_type' => 'FOO',
            'iparapheur_sous_type' => 'BAR',
            'libelle' => 'LIBELLE',
        ]);
        $donneesFormulaire->addFileFromData('document', 'test éàê accent.pdf', 'test');

        $this->triggerActionOnDocument($id_d, 'send-iparapheur');
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');

        $this->triggerActionOnDocument($id_d, 'verif-iparapheur');
        $this->assertLastMessage('La signature a été récupérée');

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertSame('2024-02-08', $donnesFormulaire->get('parapheur_date_signature'));
    }
}
