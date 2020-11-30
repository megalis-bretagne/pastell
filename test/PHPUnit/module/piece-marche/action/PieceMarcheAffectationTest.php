<?php

class PieceMarcheAffectationTest extends PastellMarcheTestCase
{
    private $id_d;
    private $parametragePieceMarche;


    /**
     * @throws Exception
     */

    protected function setUp()
    {
        parent::setUp();
        $this->parametragePieceMarche = $this->createConnecteurParametragePieceMarche('piece-marche');
        $this->id_d = $this->createDocument('piece-marche');
    }

    /**
     * @throws Exception
     */
    public function testAffectationPieces()
    {

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $select_piece = $donneesFormulaire->getFieldData('type_piece_marche')->getField()->getSelect();

        foreach ($select_piece as $type_piece_marche => $libelle_piece) {
            $donneesFormulaire->setData('type_piece_marche', $type_piece_marche);

            $result = $this->documentAction($this->id_d, 'affectation');
            if (! $result) {
                echo $this->getObjectInstancier()->getInstance('ActionExecutorFactory')->getLastMessage();
            }
            $this->assertTrue($result);

            $array_param = $this->parametragePieceMarche->getParametragePiece($type_piece_marche);

            foreach ($array_param as $key => $value) {
                $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
                $this->assertEquals($value, $donneesFormulaire->get($key));

                $this->assertTrue($donneesFormulaire->isEditable('libelle'));
            }
        }
    }

    public function sorting()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $select_piece = $donneesFormulaire->getFieldData('type_piece_marche')->getField()->getSelect();
        setlocale(LC_COLLATE, 'fr_FR.UTF8');

        uasort($select_piece, 'strcoll');

        var_export($select_piece);
    }
}
