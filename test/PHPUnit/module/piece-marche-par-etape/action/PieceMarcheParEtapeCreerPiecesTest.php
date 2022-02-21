<?php

class PieceMarcheParEtapeCreerPiecesTest extends PastellMarcheTestCase
{
    private const FILENAME_PIECE1 = "2018BPU.pdf";
    private const FILENAME_PIECE2 = "2018CCAP.pdf";
    private const ACTION_NAME = "creer-piece-marche";

    private $id_d;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $result = $this->getInternalAPI()->post(
            "/Document/" . PastellTestCase::ID_E_COL,
            array('type' => 'piece-marche-par-etape')
        );
        $this->id_d = $result['id_d'];
    }

    private function renseigneDoc()
    {

        $this->getInternalAPI()->patch(
            "/entite/1/document/$this->id_d",
            array('libelle' => 'Test marché numéro 2018REF201810',
                'numero_marche' => '2018REF201810',
                'type_marche' => 'T',
                'numero_consultation' => 'Consultation 2018REF201810',
                'type_consultation' => 'MAPA',
                'etape' => 'ONR',
                'soumissionnaire' => 'entreprise xx',
                'date_document' => '2018-10-05',
            )
        );
    }


    /**
     * @throws Exception
     */
    private function postPiecesLot()
    {

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $donneesFormulaire->addFileFromCopy('piece', self::FILENAME_PIECE1, __DIR__ . "/../fixtures/" . self::FILENAME_PIECE1, 0);
        $donneesFormulaire->addFileFromCopy('piece', self::FILENAME_PIECE2, __DIR__ . "/../fixtures/" . self::FILENAME_PIECE2, 1);
    }

    /**
     * @throws Exception
     */
    public function testCreerPresencePieceKO()
    {

        $this->renseigneDoc();

        $this->assertFalse(
            $this->triggerActionOnDocument($this->id_d, self::ACTION_NAME)
        );
        $this->assertLastMessage('ERREUR : Les fichiers Pièces sont manquants.');
    }

    /**
     * @throws Exception
     */
    public function testCreerTypologieKO()
    {

        $this->renseigneDoc();
        $this->postPiecesLot();

        $this->assertFalse(
            $this->triggerActionOnDocument($this->id_d, self::ACTION_NAME)
        );
        $this->assertLastMessage('ERREUR : La typologie des pièces est manquante.');
    }


    /**
     * @throws Exception
     */
    public function testCreationOK()
    {

        $this->renseigneDoc();
        $this->postPiecesLot();

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $donneesFormulaire->setData('type_pj', '["BPU","CCAP"]');
        $this->triggerActionOnDocument($this->id_d, self::ACTION_NAME);

        $this->assertTrue(
            $this->triggerActionOnDocument($this->id_d, self::ACTION_NAME)
        );

        // 2 document(s) Pièce de Marché créé(s): ...
        $last_message = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class)->getLastMessage();
        $this->assertEquals(substr($last_message, 0, 1), '2');
    }
}
