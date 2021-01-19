<?php

class SignatureRemordTest extends PastellTestCase
{
    use SoapUtilitiesTestTrait;

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testSignatureRemordIparapheur()
    {
        $this->mockSoapClient(
            function ($soapMethod, $arguments) {
                if ($soapMethod === 'CreerDossier') {
                    return json_decode(
                        '{"MessageRetour":{"codeRetour":"OK","message":"","severite":"INFO"}}'
                    );
                }
                return json_decode(
                    '{"MessageRetour":{"codeRetour":"OK","message":"","severite":"INFO"}}'
                );
            }
        );

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

        $this->triggerActionOnDocument($document_info['id_d'], 'remord-iparapheur');
        $this->assertLastMessage("L'action remord-iparapheur n'existe pas.");
        // Quand remord-iparapheur sera implémenté pour document-a-signer:
        // $this->assertLastMessage("Le droit de remord a été exercé sur le dossier");
    }
}
