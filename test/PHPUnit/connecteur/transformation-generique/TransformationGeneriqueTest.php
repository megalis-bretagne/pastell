<?php

class TransformationGeneriqueTest extends PastellTestCase
{
    /**
     * @return TransformationGenerique
     * @throws DonneesFormulaireException
     */
    private function getConnecteur()
    {
        $id_ce = $this->createConnector(
            'transformation-generique',
            'Transformation generique'
        )['id_ce'];

        $connecteurConfig = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

        $connecteurConfig->addFileFromCopy(
            'definition',
            "definition.json",
            __DIR__ . "/fixtures/definition.json"
        );
        return $this->getConnecteurFactory()->getConnecteurById($id_ce);
    }

    /**
     * @throws DonneesFormulaireException
     * @throws UnrecoverableException
     */
    public function testExtraction()
    {
        $transformationGenerique = $this->getConnecteur();
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $transformationGenerique->transform($donneesFormulaire, []);
        $this->assertEquals("bar", $donneesFormulaire->get('foo'));
    }

    public function testTestTransform()
    {
        $transformationGenerique = $this->getConnecteur();
        $this->assertEquals(
            '{"foo":"bar"}',
            $transformationGenerique->testTransform()
        );
    }

}
