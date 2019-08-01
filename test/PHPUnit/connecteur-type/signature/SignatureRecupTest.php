<?php

class SignatureRecupTest extends PastellTestCase
{
    use SoapUtilitiesTestTrait;

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testSignedDocumentKeepAccentuatedCharacters()
    {
        $this->mockSoapClient(
            function ($soapMethod, $arguments) {
                if ($soapMethod === 'CreerDossier') {
                    return json_decode(
                        '{"MessageRetour":{"codeRetour":"OK","message":"","severite":"INFO"}}'
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

        $connecteur_info = $this->createConnector('iParapheur', 'i-Parapheur');
        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()
            ->getConnecteurEntiteFormulaire($connecteur_info['id_ce']);
        $connecteurDonneesFormulaire->setTabData([
            'iparapheur_wsdl' => 'https://foo',
        ]);
        $this->associateFluxWithConnector($connecteur_info['id_ce'], 'document-a-signer', 'signature');

        $document_info = $this->createDocument('document-a-signer');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document_info['id_d']);
        $donneesFormulaire->setTabData([
            'iparapheur_type' => 'FOO',
            'iparapheur_sous_type' => 'BAR',
            'libelle' => 'LIBELLE',
        ]);
        $donneesFormulaire->addFileFromData('document', 'test éàê accent.pdf', 'test');

        $this->triggerActionOnDocument($document_info['id_d'], 'send-iparapheur');
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');

        $this->triggerActionOnDocument($document_info['id_d'], 'verif-iparapheur');
        $this->assertLastMessage('La signature a été récupérée');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document_info['id_d']);
        $this->assertSame(
            'test éàê accent_signe.pdf',
            $donneesFormulaire->getFileName('document')
        );

    }
}