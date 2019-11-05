<?php

class FournisseurCommandeReceptionParapheurTest extends PastellTestCase
{
    use SoapUtilitiesTestTrait;

    /**
     * @throws Exception
     */
    public function testVerifIparapheur()
    {
        $this->mockSoapClient(
            function ($soapMethod, $arguments) {
                if ($soapMethod === 'CreerDossier') {
                    return json_decode('{"MessageRetour":{"codeRetour":"OK","message":"message.","severite":"INFO"}}', false);
                }
                if ($soapMethod === 'GetHistoDossier') {
                    return json_decode(json_encode([
                        'LogDossier' => [
                            [
                                'timestamp' => 1,
                                'annotation' => 'annotation',
                                'status' => 'status'

                            ],
                        ]
                    ]), false);
                }
                throw new UnrecoverableException("Unexpected call to SOAP method : $soapMethod");
            }
        );

        $connector = $this->createConnector('iParapheur', 'parapheur');
        $this->configureConnector($connector['id_ce'], [
            'iparapheur_wsdl' => 'wsdl'
        ]);
        $this->associateFluxWithConnector($connector['id_ce'], 'commande-generique', 'signature');

        $document = $this->createDocument('commande-generique');
        $this->configureDocument($document['id_d'], [
            'libelle' => 'Libelle',
            'nom_fournisseur' => 'FOURNISSEUR',
            'mail_fournisseur' => 'mail@example.org',
            'envoi_signature' => true
        ]);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy(
            'commande',
            'vide.pdf',
            __DIR__ . "/../fixtures/vide.pdf"
        );

        $this->triggerActionOnDocument($document['id_d'], 'send-iparapheur');
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $this->assertNotEmpty($donneesFormulaire->get('iparapheur_dossier_id'));

        $this->triggerActionOnDocument($document['id_d'], 'verif-iparapheur');
        $this->assertLastMessage('01/01/1970 01:00:00 : [status] annotation');

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $this->assertSame(
            '01/01/1970 01:00:00 : [status] annotation',
            $donneesFormulaire->get('parapheur_last_message')
        );
    }
}
