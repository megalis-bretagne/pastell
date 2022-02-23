<?php

class PieceMarcheLotExtraireTest extends PastellMarcheTestCase
{
    private const FILENAME_ZIP = "exemple-zip-pieces.zip";
    private const FILENAME_PDF = "2018BPU.pdf";

    private $id_d;

    private $tmp_folder_zip;
    private $tmp_folder_pdf;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->id_d = $this->createDocument('piece-marche-par-etape')['id_d'];
        $this->tmp_folder_zip = $this->createDirectory();
        $this->tmp_folder_pdf = $this->createDirectoryPDF();
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $tmpFolder = new TmpFolder();
        $tmpFolder->delete($this->tmp_folder_zip);
        $tmpFolder->delete($this->tmp_folder_pdf);
    }

    /**
     * @throws Exception
     */
    private function createDirectory()
    {
        $tmpFolder = new TmpFolder();

        $tmp_folder_zip = $tmpFolder->create();
        copy(__DIR__ . "/../fixtures/" . self::FILENAME_ZIP, $tmp_folder_zip . "/" . self::FILENAME_ZIP);
        return $tmp_folder_zip;
    }

    /**
     * @throws Exception
     */
    public function testExtraction()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $donneesFormulaire->setTabData(['montant' => '42']);
        $donneesFormulaire->addFileFromCopy('zip_etape', 'archive.zip', $this->tmp_folder_zip . "/" . self::FILENAME_ZIP);
        $this->assertTrue(
            $this->triggerActionOnDocument($this->id_d, CommonExtractionAction::ACTION_NAME_SYNCHRONE)
        );
        $this->assertLastMessage("Extraction terminée");
        $this->assertLastDocumentAction(CommonExtractionAction::ACTION_NAME_SYNCHRONE, $this->id_d);

        $info = $this->getInternalAPI()->get("/entite/1/document/$this->id_d");

        $this->assertEquals(array (
            0 => '2018BPU.pdf',
            1 => '2018CCAP.pdf',
            2 => '2018CCTP.pdf',
            3 => '2018LR.pdf',
            4 => '2018RC.pdf',
        ), $info['data']['piece']);

        $doc = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $this->assertTrue($doc->isEditable('libelle'));
    }

    /**
     * @throws Exception
     */
    private function createDirectoryPDF()
    {
        $tmpFolder = new TmpFolder();

        $tmp_folder_pdf = $tmpFolder->create();
        copy(__DIR__ . "/../fixtures/" . self::FILENAME_PDF, $tmp_folder_pdf . "/" . self::FILENAME_PDF);
        return $tmp_folder_pdf;
    }

    /**
     * @throws Exception
     */

    public function testExtractionPDF()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $donneesFormulaire->addFileFromCopy('zip_etape', 'archive.zip', $this->tmp_folder_pdf . "/" . self::FILENAME_PDF);

        $this->assertFalse(
            $this->triggerActionOnDocument($this->id_d, CommonExtractionAction::ACTION_NAME_SYNCHRONE)
        );

        $doc = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $this->assertTrue($doc->isEditable('libelle'));
    }
}
