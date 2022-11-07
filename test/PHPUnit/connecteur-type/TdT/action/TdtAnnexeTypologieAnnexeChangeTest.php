<?php

class TdtAnnexeTypologieAnnexeChangeTest extends PastellTestCase
{
    /**
     * @return mixed
     * @throws NotFoundException
     * @throws Exception
     */
    private function configureAndCreateDocument()
    {

        $connecteur_info = $this->createConnector("fakeTdt", "Bouchon tdt");

        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()
            ->getConnecteurEntiteFormulaire($connecteur_info['id_ce']);

        $connecteurDonneesFormulaire->addFileFromCopy(
            'classification_file',
            "classification.xml",
            __DIR__ . "/../../../module/actes-generique/fixtures/classification.xml"
        );
        $this->associateFluxWithConnector($connecteur_info['id_ce'], "actes-generique", "TdT");


        $document_info = $this->createDocument('actes-generique');
        $id_d = $document_info['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document_info['id_d']);
        $donneesFormulaire->setTabData([
            'acte_nature' => 3,
        ]);

        $donneesFormulaire->addFileFromData('arrete', 'arrete.pdf', "foo");
        $donneesFormulaire->addFileFromData('autre_document_attache', 'annexe1.pdf', "bar", 0);
        $donneesFormulaire->addFileFromData('autre_document_attache', 'annexe2.pdf', "baz", 1);
        $donneesFormulaire->addFileFromData('autre_document_attache', 'annexe3.pdf', "bazz", 2);

        $info = $this->getInternalAPI()->patch(
            "/entite/1/document/{$document_info['id_d']}/externalData/type_piece",
            ['type_pj' => ['41_NC','22_DP','22_AV','22_TA']]
        );
        $this->assertEquals('41_NC', $info['data']['type_acte']);
        $this->assertEquals(
            '["22_DP","22_AV","22_TA"]',
            $info['data']['type_pj']
        );

        $this->assertEquals('4 fichier(s) typé(s)', $info['data']['type_piece']);

        $expectedJson = [
            [
                "filename" => "arrete.pdf",
                "typologie" => "Notification de création ou de vacance de poste (41_NC)",
            ],
            [
                "filename" => "annexe1.pdf",
                "typologie" => "Document photographique (22_DP)",
            ],
            [
                "filename" => "annexe2.pdf",
                "typologie" => "Avis (22_AV)",
            ],
            [
                "filename" => "annexe3.pdf",
                "typologie" => "Tableau (22_TA)"
            ]
        ];

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedJson),
            $donneesFormulaire->getFileContent('type_piece_fichier')
        );

        return $id_d;
    }

    /**
     * @throws NotFoundException
     */
    public function testAddFile()
    {
        $id_d = $this->configureAndCreateDocument();

        $info = $this->getInternalAPI()->post(
            "/entite/1/document/$id_d/file/autre_document_attache/3",
            ['file_content' => "toto","file_name" => 'annexe4.xml']
        );

        $this->assertArrayNotHasKey('type_piece', $info['content']['data']);
        $this->assertEquals('41_NC', $info['content']['data']['type_acte']);
        $this->assertEquals(
            '["22_DP","22_AV","22_TA",""]',
            $info['content']['data']['type_pj']
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEmpty(
            $donneesFormulaire->getFileContent('type_piece_fichier')
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testDeleteFile()
    {
        $id_d = $this->configureAndCreateDocument();
        $info = $this->getInternalAPI()->delete("/entite/1/document/$id_d/file/autre_document_attache/1");

        $this->assertArrayNotHasKey('type_piece', $info['data']);
        $this->assertEquals('41_NC', $info['data']['type_acte']);
        $this->assertEquals(
            '["22_DP","22_TA"]',
            $info['data']['type_pj']
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertEmpty(
            $donneesFormulaire->getFileContent('type_piece_fichier')
        );
    }


    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testRemoveLastAnnexe()
    {
        $connecteur_info = $this->createConnector('fakeTdt', 'Bouchon tdt');

        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()
            ->getConnecteurEntiteFormulaire($connecteur_info['id_ce']);

        $connecteurDonneesFormulaire->addFileFromCopy(
            'classification_file',
            'classification.xml',
            __DIR__ . '/../../../module/actes-generique/fixtures/classification.xml'
        );
        $this->associateFluxWithConnector($connecteur_info['id_ce'], 'actes-generique', 'TdT');


        $document_info = $this->createDocument('actes-generique');
        $id_d = $document_info['id_d'];
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document_info['id_d']);
        $donneesFormulaire->setTabData([
            'acte_nature' => 3,
        ]);

        $donneesFormulaire->addFileFromData('arrete', 'arrete.pdf', 'foo');
        $donneesFormulaire->addFileFromData('autre_document_attache', 'annexe1.pdf', 'bar', 0);

        $this->getInternalAPI()->patch(
            "/entite/1/document/{$document_info['id_d']}/externalData/type_piece",
            ['type_pj' => ['41_NC', '22_DP']]
        );

        $info = $this->getInternalAPI()->delete("/entite/1/document/$id_d/file/autre_document_attache/0");
        $this->assertSame(
            '[]',
            $info['data']['type_pj']
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testAddWrongTypePJ()
    {
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('Le type_pj «22_XX» ne correspond pas pour la nature et la classification sélectionnée');

        $id_d = $this->configureAndCreateDocument();

        $this->getInternalAPI()->patch(
            "/entite/1/document/{$id_d}/externalData/type_piece",
            ['type_pj' => ['41_NC','22_DP','22_AV','22_XX']]
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testFailCountTypePJ()
    {
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('Le nombre de type_pj fourni «3» ne correspond pas au nombre de documents (acte et annexes) «4»');

        $id_d = $this->configureAndCreateDocument();

        $this->getInternalAPI()->patch(
            "/entite/1/document/{$id_d}/externalData/type_piece",
            ['type_pj' => ['41_NC','22_DP','22_AV']]
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testWrongArrayTypePJ()
    {
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('Aucun tableau type_pj fourni');

        $id_d = $this->configureAndCreateDocument();

        $this->getInternalAPI()->patch(
            "/entite/1/document/{$id_d}/externalData/type_piece",
            ['type_pj = ["41_NC","22_DP","22_AV","22_TA"]']
        );
    }
}
