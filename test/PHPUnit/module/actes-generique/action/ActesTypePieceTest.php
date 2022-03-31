<?php

class ActesTypePieceTest extends PastellTestCase
{
    private function postActes()
    {
        $fileUploader = new FileUploaderMock();
        $fileUploader->setFiles(['file_content' => file_get_contents(__DIR__ . "/../fixtures/classification.xml")]);
        $this->getInternalAPI()->setFileUploader($fileUploader);

        $this->getInternalAPI()->post(
            "/entite/1/connecteur/2/file/classification_file"
        );

        $info = $this->getInternalAPI()->post(
            "/entite/1/document",
            ['type' => 'actes-generique']
        );

        $id_d =  $info['id_d'];

        $this->getInternalAPI()->post(
            "/entite/1/document/$id_d/file/arrete",
            ['file_name' => 'arrete.pdf','file_content' => __DIR__ . "/../fixtures/Delib Adullact.pdf"]
        );

        $this->getInternalAPI()->patch(
            "/entite/1/document/$id_d",
            ['acte_nature' => '3','classification' => '4.1']
        );

        return $id_d;
    }

    public function testDisplayAPI()
    {
        $id_d = $this->postActes();
        $info = $this->getInternalAPI()->get("/entite/1/document/$id_d/externalData/type_piece");

        $expected =  [
            'actes_type_pj_list' =>
                 [
                    '99_AI' => 'Acte individuel (99_AI)',
                    '22_AR' => 'Accusé de réception (22_AR)',
                    '22_AG' => 'Agrément ou certificat (22_AG)',
                    '22_AT' => 'Attestation (22_AT)',
                    '41_AT' => 'Attestation (41_AT)',
                    '22_AV' => 'Avis (22_AV)',
                    '41_CA' => 'Avis de commission administrative paritaire (41_CA)',
                    '41_CM' => 'Avis de la commission mixte paritaire (41_CM)',
                    '22_CO' => 'Convention (22_CO)',
                    '22_DD' => 'Demande (22_DD)',
                    '22_DP' => 'Document photographique (22_DP)',
                    '22_DN' => 'Décision (22_DN)',
                    '41_DE' => 'Délibération établissant la liste de postes à pourvoir (41_DE)',
                    '41_IC' => 'Information du centre de gestion (41_IC)',
                    '22_LE' => 'Lettre (22_LE)',
                    '22_NE' => 'Notice explicative (22_NE)',
                    '41_NC' => 'Notification de création ou de vacance de poste (41_NC)',
                    '22_PN' => 'Plans (22_PN)',
                    '22_PE' => 'Présentation des états initiaux et futurs (22_PE)',
                    '22_TA' => 'Tableau (22_TA)',
                ],
            'pieces' =>
                 [
                    0 => 'arrete.pdf',
                ],
        ];

        $this->assertEquals($expected, $info);
    }

    public function testGo()
    {
        $id_d = $this->postActes();
        $info = $this->getInternalAPI()->patch("/entite/1/document/$id_d/externalData/type_piece", ['type_pj' => ['41_NC']]);
        $this->assertEquals('1 fichier(s) typé(s)', $info['data']['type_piece']);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $type_piece_fichier =  $donneesFormulaire->getFileContent('type_piece_fichier');
        $this->assertEquals(
            '[{"filename":"arrete.pdf","typologie":"Notification de cr\u00e9ation ou de vacance de poste (41_NC)"}]',
            $type_piece_fichier
        );
        $this->assertEquals('41_NC', $info['data']['type_acte']);
    }
}
