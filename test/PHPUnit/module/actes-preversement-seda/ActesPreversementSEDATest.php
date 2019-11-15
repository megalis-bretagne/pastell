<?php

class ActesPreversementSEDATest extends PastellTestCase
{

    const FLUX_ID = 'actes-preversement-seda';

    /**
     * @throws NotFoundException
     */
    public function testCasNominal()
    {
        $result = $this->createDocument(self::FLUX_ID);
        $this->assertNotEmpty($result['id_d']);

        $info['id_d'] = $result['id_d'];
        $info['id_e'] = PastellTestCase::ID_E_COL;
        $info['titre'] = "Test d'un versement";

        $this->configureDocument($info['id_d'], $info);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        $donneesFormulaire->addFileFromCopy(
            'enveloppe_metier',
            '034-491011698-20171207-CL20171227_06-DE-1-1_0.xml',
            __DIR__ . '/fixtures/034-491011698-20171207-CL20171227_06-DE-1-1_0.xml'
        );
        $donneesFormulaire->addFileFromCopy(
            'document',
            '32_DP-034-491011698-20171207-CL20171227_06-DE-1-1_1.pdf',
            __DIR__ . '/fixtures/32_DP-034-491011698-20171207-CL20171227_06-DE-1-1_1.pdf'
        );
        $donneesFormulaire->addFileFromCopy(
            'document',
            '034-491011698-20171207-CL20171227_06-DE-1-1_2.pdf',
            __DIR__ . '/fixtures/32_DP-034-491011698-20171207-CL20171227_06-DE-1-1_2.pdf',
            1
        );
        $donneesFormulaire->addFileFromCopy(
            'aractes',
            '034-491011698-20171207-CL20171227_06-DE-1-2.xml',
            __DIR__ . '/fixtures/034-491011698-20171207-CL20171227_06-DE-1-2.xml'
        );

        $result = $this->getInternalAPI()->post("/entite/{$info['id_e']}/document/{$info['id_d']}/action/create-acte");

        preg_match("#Création du document Pastell (.*)#", $result['message'], $matches);
        $id_d = $matches[1];

        $result = $this->getInternalAPI()->get("/entite/{$info['id_e']}/document/$id_d");

        $this->assertSame('32_DP', $result['data']['type_acte']);
        $this->assertSame('[]', $result['data']['type_pj']);

        $this->assertEquals("3.2", $result['data']['classification']);
        $this->assertEquals("importation", $result['last_action']['action']);
    }

    /**
     * @throws NotFoundException
     */
    public function testFilenameDifferentThanEnveloppeName()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Aucun fichier ayant comme nom « 32_DP-034-491011698-20171207-CL20171227_06-DE-1-1_1.pdf » n'a été trouvé"
        );

        $document = $this->createDocument(self::FLUX_ID);
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $donneesFormulaire->addFileFromCopy(
            'enveloppe_metier',
            '034-491011698-20171207-CL20171227_06-DE-1-1_0.xml',
            __DIR__ . '/fixtures/034-491011698-20171207-CL20171227_06-DE-1-1_0.xml'
        );
        $donneesFormulaire->addFileFromCopy(
            'document',
            '99_SE-034-491011698-20171207-CL20171227_06-DE-1-1_1.pdf',
            __DIR__ . '/fixtures/32_DP-034-491011698-20171207-CL20171227_06-DE-1-1_1.pdf'
        );
        $donneesFormulaire->addFileFromCopy(
            'aractes',
            '034-491011698-20171207-CL20171227_06-DE-1-2.xml',
            __DIR__ . '/fixtures/034-491011698-20171207-CL20171227_06-DE-1-2.xml'
        );

        $this->getInternalAPI()->post(
            sprintf(
                "/entite/%s/document/%s/action/create-acte",
                PastellTestCase::ID_E_COL,
                $document['id_d']
            )
        );
    }
}
