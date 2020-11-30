<?php

class PESMarcheEnvoiSAETest extends PastellMarcheTestCase
{

    private const PES_MARCHE = 'pes-marche';

    /**
     * @throws Exception
     */
    public function testOK()
    {

        $this->createConnecteurSEDA(self::PES_MARCHE);
        $this->createConnecteurSAE(self::PES_MARCHE);


        $result = $this->getInternalAPI()->post(
            "/Document/" . PastellTestCase::ID_E_COL,
            array('type' => self::PES_MARCHE)
        );
        $id_d = $result['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            'objet' => 'test'
        ]);

        $donneesFormulaire->addFileFromCopy('fichier_pes', 'PESALR2_XYZ.xml', __DIR__ . "/../fixtures/exemple_marche_contrat_initial_nov2017.xml");
        $donneesFormulaire->addFileFromCopy('fichier_reponse', 'PESALR2_XYZ.xml', __DIR__ . "/../fixtures/exemple_marche_contrat_initial_nov2017.xml");


        $result = $this->getObjectInstancier()->getInstance('ActionExecutorFactory')->executeOnDocument(
            PastellTestCase::ID_E_COL,
            0,
            $id_d,
            'send-archive'
        );

        if (! $result) {
            echo $donneesFormulaire->getFileContent('sae_bordereau');
            throw new Exception(($this->getObjectInstancier()->getInstance('ActionExecutorFactory')->getLastMessage()));
        }


        $xml = simplexml_load_file($donneesFormulaire->getFilePath('sae_bordereau'));
        $children = $xml->children(SedaValidation::SEDA_V_1_0_NS);


        $children->{'Date'} = 'NOT TESTABLE';
        $children->{'TransferIdentifier'} = "NOT TESTABLE";

        //echo $xml->asXML();

        //file_put_contents(__DIR__."/../fixtures/bordereau.xml",$xml->asXML());

        $this->assertStringEqualsFile(__DIR__ . "/../fixtures/bordereau.xml", $xml->asXML());

        $donneesFormulaire->getFilePath('sae_bordereau');

        $sae_archive = $donneesFormulaire->getFileContent('sae_archive');

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        file_put_contents("$tmp_folder/archive.tgz", $sae_archive);
        exec("tar xvzf $tmp_folder/archive.tgz -C $tmp_folder");

        $this->assertEquals(['.','..','PESALR2_XYZ.xml','archive.tgz'], scandir("$tmp_folder/"));

        $tmpFolder->delete($tmp_folder);
    }
}
