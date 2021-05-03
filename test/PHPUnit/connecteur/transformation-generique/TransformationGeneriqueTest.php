<?php

class TransformationGeneriqueTest extends PastellTestCase
{

    /**
     * @return bool|Connecteur
     * @throws DonneesFormulaireException
     * @throws Exception
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
        $transformationGenerique->transform($donneesFormulaire);
        $this->assertEquals("bar", $donneesFormulaire->get('foo'));
    }

    public function testTestTransform()
    {
        $transformationGenerique = $this->getConnecteur();
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $this->assertEquals(
            '{"foo":"bar","envoi_signature":"true","titre":"Ceci est mon titre"}',
            $transformationGenerique->testTransform($donneesFormulaire)
        );
    }
}