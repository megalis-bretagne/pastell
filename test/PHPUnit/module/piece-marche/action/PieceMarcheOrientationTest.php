<?php

class PieceMarcheOrientationTest extends PastellMarcheTestCase
{
    private $id_d;


    /**
     * @throws Exception
     */

    protected function setUp(): void
    {
        parent::setUp();
        $this->id_d = $this->createDocument('piece-marche')['id_d'];
    }

    /**
     * @throws Exception
     */
    public function testOrientationPiecesPrepareGedWhithJournal()
    {

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
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

        $this->getInternalAPI()->patch(
            "/entite/1/document/$this->id_d",
            array("envoi_ged" => "on")
        );

        $this->assertTrue(
            $this->triggerActionOnDocument($this->id_d, 'orientation')
        );
        $this->assertLastMessage('Changement d\'état : modification -> preparation-send-ged');
        $this->assertLastDocumentAction('preparation-send-ged', $this->id_d);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);

        $this->assertEquals($donneesFormulaire->getFileName('journal'), 'journal.json');

        $json_content = $donneesFormulaire->getFileContent('journal');
        $data = json_decode($json_content);
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE);
    }
}
