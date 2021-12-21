<?php

class SAEEnvoiHeliosTest extends PastellTestCase
{
    public function testEnvoiSAE()
    {
        $this->createConnecteurForTypeDossier('helios-generique', "fakeSAE");
        $this->createConnecteurForTypeDossier('helios-generique', "FakeSEDA");

        $id_d = $this->createDocument('helios-generique')['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->setTabData([
        ]);

        $this->assertTrue(
            $this->triggerActionOnDocument($id_d, 'send-archive')
        );
        $this->assertLastMessage("Le document a été envoyé au SAE");
        $this->assertLastDocumentAction('send-archive', $id_d);
    }
}
