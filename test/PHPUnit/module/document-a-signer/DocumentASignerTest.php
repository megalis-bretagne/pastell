<?php

class DocumentASignerTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    private function createAllConnecteur()
    {
        $connecteur_info = $this->createConnector('fakeIparapheur', "Bouchon parapheur");

        $this->getDonneesFormulaireFactory()
            ->getConnecteurEntiteFormulaire($connecteur_info['id_ce'])
            ->setTabData([
                'iparapheur_type' => 'Document',
                'iparapheur_envoi_status' => 'ok',
                'iparapheur_retour' => 'Archive'
            ]);

        $this->associateFluxWithConnector($connecteur_info['id_ce'], 'document-a-signer', 'signature');

        $connecteur_info = $this->createConnector('FakeGED', "Bouchon GED");
        $this->associateFluxWithConnector($connecteur_info['id_ce'], 'document-a-signer', 'GED');
    }

    /**
     * @throws Exception
     */
    public function testCasNominal()
    {
        $this->createAllConnecteur();

        $document_info = $this->createDocument('document-a-signer');
        $id_d = $document_info['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->setTabData([
            'libelle' => 'Test',
            'envoi_ged' => "On",
            "envoi_auto" => "On",
            "iparapheur_type" => "test",
            "iparapheur_sous_type" => "test"
        ]);

        $donneesFormulaire->addFileFromData('document', 'document.txt', "foo");

        $this->assertActionPossible(['modification','supression','send-iparapheur'], $id_d);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "send-iparapheur")
        );

        $this->assertLastDocumentAction('send-iparapheur', $id_d);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "verif-iparapheur")
        );
        $this->assertLastDocumentAction('recu-iparapheur', $id_d);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "send-ged")
        );

        $this->assertLastDocumentAction('send-ged', $id_d);
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testWhenCheckGedAfterRecuIparapheur()
    {
        $this->createAllConnecteur();
        $document_info = $this->createDocument('document-a-signer');
        $id_d = $document_info['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->setTabData([
            'libelle' => 'Test',
            "iparapheur_type" => "test",
            "iparapheur_sous_type" => "test"
        ]);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "send-iparapheur")
        );

        $this->assertLastDocumentAction('send-iparapheur', $id_d);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "verif-iparapheur")
        );
        $this->assertLastDocumentAction('recu-iparapheur', $id_d);
        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, "orientation")
        );
        $this->assertLastDocumentAction('recu-iparapheur-etat', $id_d);

        $this->assertActionPossible(['modification','supression'], $id_d);
        $donneesFormulaire->setTabData([
            'envoi_ged' => 'On',
        ]);
        $this->assertActionPossible(['modification','supression','send-ged'], $id_d);
    }
}
