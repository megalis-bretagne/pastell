<?php

require_once __DIR__ . "/../../../../connecteur/fakeSAE/FakeSAE.class.php";
require_once __DIR__ . "/../../../../connecteur/seda-ng/SedaNG.class.php";

class SedaNGExtractZipCommandTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testCasNominal()
    {

        $this->createConnecteurForTypeDossier('pdf-generique', FakeSAE::CONNECTEUR_ID);

        $id_ce = $this->createConnecteurForTypeDossier('pdf-generique', SedaNG::CONNECTEUR_ID);


        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

        $connecteurDonneesFormulaire->addFileFromCopy('schema_rng', "profil.rng", __DIR__ . "/fixtures/PROFIL_AVEC_ZIP_schema.rng");
        $connecteurDonneesFormulaire->addFileFromCopy('profil_agape', 'profil.xml', __DIR__ . "/fixtures/PROFIL_AVEC_ZIP.xml");
        $connecteurDonneesFormulaire->addFileFromData('connecteur_info_content', 'connecteur_info_content.json', json_encode(['']));


        $id_d = $this->createDocument('pdf-generique')['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->addFileFromCopy(
            "document",
            "document.zip",
            __DIR__ . "/fixtures/42007_achat_de_materiel_de_bureau.zip"
        );

        $this->triggerActionOnDocument($id_d, 'send-archive');

        //echo  $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class)->getLastMessage();

        //echo $donneesFormulaire->getFileContent('sae_bordereau');
        $this->assertLastMessage("Le document a été envoyé au SAE");


        $xml = simplexml_load_file($donneesFormulaire->getFilePath('sae_bordereau'));
        $children = $xml->children(SedaValidation::SEDA_V_1_0_NS);
        $children->{'TransferIdentifier'} = "NOT TESTABLE";
        $children->{'Date'} = 'NOT TESTABLE';

        //file_put_contents(__DIR__."/fixtures/bordereau-avec-zip.xml",$xml->asXML());

        $this->assertStringEqualsFile(__DIR__ . "/fixtures/bordereau-avec-zip.xml", $xml->asXML());
    }
}
