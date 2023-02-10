<?php

class TdtTypologieChangeByApiTest extends PastellTestCase
{
    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testAddTypeActe()
    {

        $id_d = $this->createActeGenerique();
        $donneesFormulaire = $this->setActeData($id_d);

        $info = $this->getInternalAPI()->patch("/Entite/1/document/$id_d/", ['type_acte' => '22_NE']);
        $this->assertEquals("22_NE", $info['content']['data']['type_acte']);
        $this->assertEquals("1 fichier(s) typé(s)", $info['content']['data']['type_piece']);
        $this->assertEquals(
            '[{"filename":"arrete.pdf","typologie":"Notice explicative (22_NE)"}]',
            $donneesFormulaire->getFileContent('type_piece_fichier')
        );

        $info = $this->getInternalAPI()->patch("/Entite/1/document/$id_d/", ['type_pj' => '["41_NC","22_DP"]']);
        $this->assertEquals("22_NE", $info['content']['data']['type_acte']);
        $this->assertEquals('["41_NC","22_DP"]', $info['content']['data']['type_pj']);
        $this->assertEquals("3 fichier(s) typé(s)", $info['content']['data']['type_piece']);
        static::assertJsonFileEqualsJsonFile(
            __DIR__ . '/fixtures/type_piece_fichier.json',
            $donneesFormulaire->getFilePath('type_piece_fichier')
        );
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testAddWrongTypeActe()
    {

        $id_d = $this->createActeGenerique();
        $this->setActeData($id_d);
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage("Le type de pièce «22_XX» ne correspond pas pour la nature et la classification selectionnée");
        $this->configureDocument($id_d, ['type_acte' => '22_XX']);
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testAddWrongTypePJ()
    {

        $id_d = $this->createActeGenerique();
        $this->setActeData($id_d);
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage("Le type de pièce «99_XX» ne correspond pas pour la nature et la classification selectionnée");
        $this->configureDocument($id_d, ['type_pj' => '["41_NC","99_XX"]']);
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testFailCountTypePJ()
    {
        $id_d = $this->createActeGenerique();
        $this->setActeData($id_d);
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage("Le nombre de type de pièce «1» ne correspond pas au nombre d'annexe «2»");
        $this->configureDocument($id_d, ['type_pj' => '["41_NC"]']);
    }


    /**
     * @return mixed
     * @throws Exception
     */
    private function createActeGenerique()
    {
        $connecteur_info = $this->createConnector("fakeTdt", "Bouchon tdt");

        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()
            ->getConnecteurEntiteFormulaire($connecteur_info['id_ce']);

        $connecteurDonneesFormulaire->addFileFromCopy(
            'classification_file',
            "classification.xml",
            __DIR__ . "/../../module/actes-generique/fixtures/classification.xml"
        );
        $this->associateFluxWithConnector($connecteur_info['id_ce'], "actes-generique", "TdT");

        $document_info = $this->createDocument("actes-generique");
        $id_d = $document_info['id_d'];
        return $id_d;
    }

    /**
     * @param $id_d
     * @return DonneesFormulaire
     * @throws NotFoundException
     * @throws Exception
     */
    private function setActeData($id_d)
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->setTabData([
            'acte_nature' => '3',
            'numero_de_lacte' => '1515',
        ]);


        $donneesFormulaire->addFileFromData('arrete', "arrete.pdf", "foo");

        $donneesFormulaire->addFileFromData(
            'autre_document_attache',
            "annexe1.pdf",
            "bar",
            0
        );
        $donneesFormulaire->addFileFromData(
            'autre_document_attache',
            "annexe2.pdf",
            "baz",
            1
        );
        return $donneesFormulaire;
    }
}
