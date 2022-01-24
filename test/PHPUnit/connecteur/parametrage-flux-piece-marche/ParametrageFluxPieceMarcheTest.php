<?php

class ParametrageFluxPieceMarcheTest extends PastellMarcheTestCase
{
    /** @var ParametrageFluxPieceMarche */
    private $parametragePieceMarche;

    /** @var DonneesFormulaire */
    private $donneesFormulaire;

    public function testSetPieceMarcheJsonByDefault()
    {

        $this->parametragePieceMarche = new ParametrageFluxPieceMarche();

        $this->donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();

        $this->parametragePieceMarche->setConnecteurConfig($this->donneesFormulaire);

        $this->assertTrue($this->parametragePieceMarche->setPieceMarcheJsonByDefault(), '"Le fichier par défaut parametrage-piece-marches.json n\'a pas été trouvé"');
    }


    public function testIsPieceMarcheJsonValide()
    {
        $this->parametragePieceMarche = new ParametrageFluxPieceMarche();

        $this->donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();

        $this->parametragePieceMarche->setConnecteurConfig($this->donneesFormulaire);

        $this->assertTrue($this->parametragePieceMarche->isPieceMarcheJsonValide(), '"Le fichier par défaut parametrage-piece-marches.json n\'est pas valide"');
    }
}
