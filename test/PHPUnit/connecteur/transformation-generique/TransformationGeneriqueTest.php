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
     */
    public function testExtraction()
    {
        $transformationGenerique = $this->getConnecteur();
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $transformationGenerique->transform($donneesFormulaire);
        $this->assertEquals("bar", $donneesFormulaire->get('foo'));
    }

    /**
     * @throws DonneesFormulaireException
     */
    public function testTestTransform()
    {
        $transformationGenerique = $this->getConnecteur();
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $info = $this->createDocument('test');
        $donneesFormulaire->id_d = $info['id_d'];
        $this->assertEquals(
            '{"foo":"bar","envoi_signature":"true","titre":"Ceci est mon titre","from_pa_metadata":"Bourg-en-Bresse Eric"}',
            $transformationGenerique->testTransform($donneesFormulaire)
        );
    }
}
