<?php

class IParapheurDateSignatureTest extends PastellTestCase
{
    use SoapUtilitiesTestTrait;

    /**
     * @throws NotFoundException
     */
    public function testSigne(): void
    {
        $this->mockSoapClient(function ($soapMethod) {
            if ($soapMethod === 'CreerDossier') {
                return json_decode(
                    '{"MessageRetour":{"codeRetour":"OK","message":"","severite":"INFO"}}',
                    false,
                    512,
                    JSON_THROW_ON_ERROR
                );
            }
            if ($soapMethod === 'GetHistoDossier') {
                return json_decode(json_encode([
                    'LogDossier' => [
                        0 => [
                            'timestamp' => '2024-02-07T13:22:54.380Z',
                            'nom' => 'WS User',
                            'status' => 'NonLu',
                            'annotation' => 'Création de dossier',
                        ],
                        1 => [
                            'timestamp' => '2024-02-07T13:22:54.380Z',
                            'nom' => 'WS User',
                            'status' => 'NonLu',
                            'annotation' => 'Emission du dossier',
                        ],
                        2 => [
                            'timestamp' => '2024-02-07T13:22:54.380Z',
                            'nom' => 'WS User',
                            'status' => 'NonLu',
                            'annotation' => 'Dossier déposé sur le bureau Bureau Signature 1 pour signature',
                        ],
                        3 => [
                            'timestamp' => '2024-02-07T13:22:54.582Z',
                            'nom' => 'Signe1 User',
                            'status' => 'Lu',
                            'annotation' => 'Dossier lu et prêt pour la signature',
                        ],
                        4 => [
                            'timestamp' => '2024-02-07T13:22:54.582Z',
                            'nom' => 'Signe1 User',
                            'status' => 'Signe',
                        ],
                        5 => [
                            'timestamp' => '2024-02-08T13:25:58.219Z',
                            'nom' => 'Signe2 User',
                            'status' => 'Lu',
                            'annotation' => 'Dossier lu et prêt pour la signature',
                        ],
                        6 => [
                            'timestamp' => '2024-02-08T13:25:58.219Z',
                            'nom' => 'Signe2 User',
                            'status' => 'Signe',
                        ],
                        7 => [
                            'timestamp' => '2024-02-08T13:25:58.219Z',
                            'nom' => 'Signe2 User',
                            'status' => 'Archive',
                            'annotation' => 'Circuit terminé, dossier archivable',
                        ],
                    ],
                    'MessageRetour' => [
                        'codeRetour' => 'OK',
                        'message' => '',
                        'severite' => 'INFO'
                    ]
                ], JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
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
            ], JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
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

        $domDocument = new DOMDocument();
        $domDocument->preserveWhiteSpace = false;
        $domDocument->formatOutput = true;
        $domDocument->loadXML($donneesFormulaire->getFileContent('iparapheur_historique'));

        static::assertStringEqualsFile(
            __DIR__ . '/fixtures/iparapheur-historique-Signe-2X.xml',
            $domDocument->saveXML()
        );

        static::assertSame('2024-02-08', $donnesFormulaire->get('parapheur_date_signature'));
    }
}
