<?php

class PieceMarcheEnvoiSAETest extends PastellMarcheTestCase
{

    private const PIECE_MARCHE = 'piece-marche';

    /**
     * @throws Exception
     */
    public function testOK()
    {

        $this->createConnecteurSEDA(self::PIECE_MARCHE);
        $this->createConnecteurSAE(self::PIECE_MARCHE);


        $result = $this->getInternalAPI()->post(
            "/Document/" . PastellTestCase::ID_E_COL,
            array('type' => self::PIECE_MARCHE)
        );
        $id_d = $result['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            "date_document" => "2018-01-01",
            "libelle" => "mon marché",
            "numero_marche" => "1234",
            "type_marche" => "T",
            "numero_consultation" => "12",
            "type_consultation" => "MAPA",
            "etape" => "EB",
            "type_piece_marche" => "AC",
            "libelle_piece" => "pièce",
            "soumissionnaire" => "toto",
        ]);

        $donneesFormulaire->addFileFromCopy('document', 'vide.pdf', __DIR__ . "/../fixtures/vide.pdf");

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

        //$this->assertStringEqualsFile(__DIR__."/../fixtures/bordereau.xml",$xml->asXML());

        $donneesFormulaire->getFilePath('sae_bordereau');

        $sae_archive = $donneesFormulaire->getFileContent('sae_archive');

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        file_put_contents("$tmp_folder/archive.tgz", $sae_archive);
        exec("tar xvzf $tmp_folder/archive.tgz -C $tmp_folder");

        //$this->assertEquals(['.','..','archive.tgz','vide.pdf'],scandir("$tmp_folder/"));
        $this->assertFileExists($tmp_folder . '/archive.tgz');
        $this->assertFileExists($tmp_folder . '/vide.pdf');

        $tmpFolder->delete($tmp_folder);
    }
}
