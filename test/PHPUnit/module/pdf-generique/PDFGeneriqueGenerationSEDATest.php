<?php

class PDFGeneriqueGenerationSEDATest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testCasNominal()
    {

        $this->createConnecteurForTypeDossier('pdf-generique', FakeSAE::CONNECTEUR_ID);

        $id_ce = $this->createConnecteurForTypeDossier('pdf-generique', SedaNG::CONNECTEUR_ID);


        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

        $connecteurDonneesFormulaire->addFileFromCopy('schema_rng', "profil.rng", __DIR__ . "/fixtures/Profil-facture_B2B_V1.0.1.rng");
        $connecteurDonneesFormulaire->addFileFromCopy('profil_agape', 'profil.xml', __DIR__ . "/fixtures/Profil-facture_B2B_V1.0.1.xml");
        $connecteurDonneesFormulaire->addFileFromData('connecteur_info_content', 'connecteur_info_content.json', json_encode(['']));


        $id_d = $this->createDocument('pdf-generique')['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->addFileFromCopy(
            "document",
            "document.pdf",
            __DIR__ . "/fixtures/Délib Libriciel.pdf"
        );
        $donneesFormulaire->addFileFromCopy(
            "annexe",
            "annexe1.pdf",
            __DIR__ . "/fixtures/annexe1.pdf",
            0
        );
        $donneesFormulaire->addFileFromCopy(
            "annexe",
            "annexe2.pdf",
            __DIR__ . "/fixtures/annexe2.pdf",
            1
        );
        $donneesFormulaire->addFileFromCopy(
            "sae_config",
            "sae.json",
            __DIR__ . "/../../../../module/pdf-generique/fixtures/metadata-sae.json"
        );

        $this->triggerActionOnDocument($id_d, 'send-archive');

        $this->assertLastMessage("Le document a été envoyé au SAE");


        $xml = simplexml_load_file($donneesFormulaire->getFilePath('sae_bordereau'));
        $children = $xml->children(SedaValidation::SEDA_V_1_0_NS);
        $children->{'TransferIdentifier'} = "NOT TESTABLE";
        $children->{'Date'} = 'NOT TESTABLE';
        $children->{'Archive'}->{'Document'}->{'Creation'} = 'NOT TESTABLE';
        $children->{'Archive'}->{'Document'}->{'Integrity'} = 'NOT TESTABLE';
        //file_put_contents(__DIR__."/fixtures/bordereau.xml",$xml->asXML());

        $this->assertStringEqualsFile(__DIR__ . "/fixtures/bordereau.xml", $xml->asXML());
    }
}
