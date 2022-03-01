<?php

class HeliosGeneriqueGenerationSEDATest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testCasNominal()
    {

        $this->createConnecteurForTypeDossier('helios-generique', FakeSAE::CONNECTEUR_ID);

        $id_ce = $this->createConnecteurForTypeDossier('helios-generique', SedaNG::CONNECTEUR_ID);


        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

        $connecteurDonneesFormulaire->addFileFromCopy('schema_rng', "profil.rng", __DIR__ . "/../fixtures/Profil_PES_AP_v3-3.0.4_schema.rng");
        $connecteurDonneesFormulaire->addFileFromCopy('profil_agape', 'profil.xml', __DIR__ . "/../fixtures/Profil_PES_AP_v3-3.0.4.xml");
        $connecteurDonneesFormulaire->addFileFromData('connecteur_info_content', 'connecteur_info_content.json', json_encode(['']));


        $id_d = $this->createDocument('helios-generique')['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->addFileFromCopy(
            "fichier_pes",
            "fichier_pes.xml",
            __DIR__ . "/../fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml"
        );

        $donneesFormulaire->addFileFromCopy(
            "fichier_reponse",
            "fichier_reponse.xml",
            __DIR__ . "/../fixtures/pes_acquit_no_ack.xml"
        );

        $this->triggerActionOnDocument($id_d, 'send-archive');

        $this->assertLastMessage("Le document a été envoyé au SAE");


        $xml = simplexml_load_file($donneesFormulaire->getFilePath('sae_bordereau'));
        $children = $xml->children(SedaValidation::SEDA_V_1_0_NS);
        $children->{'TransferIdentifier'} = "NOT TESTABLE";
        $children->{'Date'} = 'NOT TESTABLE';
        //file_put_contents(__DIR__."/../fixtures/bordereau.xml",$xml->asXML());

        $this->assertStringEqualsFile(__DIR__ . "/../fixtures/bordereau.xml", $xml->asXML());
    }
}
