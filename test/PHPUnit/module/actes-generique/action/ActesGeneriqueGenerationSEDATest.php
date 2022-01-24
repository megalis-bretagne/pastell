<?php

class ActesGeneriqueGenerationSEDATest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testCasNominal()
    {

        $this->createConnecteurForTypeDossier('actes-generique', FakeSAE::CONNECTEUR_ID);

        $id_ce = $this->createConnecteurForTypeDossier('actes-generique', SedaNG::CONNECTEUR_ID);


        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

        $connecteurDonneesFormulaire->addFileFromCopy('schema_rng', "profil.rng", __DIR__ . "/../fixtures/Profil_ACTES_AP_v3_schema-3.0.5.rng");
        $connecteurDonneesFormulaire->addFileFromCopy('profil_agape', 'profil.xml', __DIR__ . "/../fixtures/Profil_ACTES_AP_v3-3.0.5.xml");
        $connecteurDonneesFormulaire->addFileFromData('connecteur_info_content', 'connecteur_info_content.json', json_encode(['']));


        $id_d = $this->createDocument('actes-generique')['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->addFileFromData("arrete", "ma_deliberation.pdf", "le contenu de ma délibération");

        $donneesFormulaire->addFileFromData("autre_document_attache", "ma_premiere_annexe.pdf", "Le contenu de ma première annexe", 0);
        $donneesFormulaire->addFileFromData("autre_document_attache", "ma_seconde_annexe.pdf", "Le contenu de ma seconde annexe", 1);

        $donneesFormulaire->addFileFromCopy("aractes", "aractes.xml", __DIR__ . "/../fixtures/aractes.xml");

        $donneesFormulaire->setTabData([
            'envoi_signature' => 1,
            'acte_nature' => '1',
            'numero_de_lacte' => '201812101413',
            'objet' => 'Achat de libriciels',
            'date_de_lacte' => '2018-12-10',
            'classification' => '1.1',
            'iparapheur_type' => 'ACTES',
            'iparapheur_sous_type' => 'DELIBERATION',
            'type_piece' => 'ok',
        ]);

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
