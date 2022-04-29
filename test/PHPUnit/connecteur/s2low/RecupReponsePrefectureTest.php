<?php

class RecupReponsePrefectureTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;

    /**
     * @throws NotFoundException
     */
    public function testLinksBetweenActes()
    {
        $acte_id_e = 2;
        $uniqueId = '034-491011698-20190829-201908291002-AI';

        $acteDocument = $this->createDocument('actes-generique', $acte_id_e);
        $this->configureDocument(
            $acteDocument['id_d'],
            ['acte_unique_id' => $uniqueId],
            $acte_id_e
        );

        $this->mockCurl([
            '/admin/users/api-list-login.php' => true,
            '/modules/actes/api/list_document_prefecture.php' => '[{"id":"3267","type":"2","related_transaction_id":"3266","number":"201908291002","unique_id": "' . $uniqueId . '","last_status_id":"21"}]',
            '/modules/actes/actes_transac_get_document.php?id=3267' => file_get_contents(__DIR__ . '/fixtures/reponse_prefecture.tar.gz'),
            '/modules/actes/api/document_prefecture_mark_as_read.php?transaction_id=3267' => true
        ]);

        $s2low = $this->createConnector('s2low', 's2low');
        $this->associateFluxWithConnector($s2low['id_ce'], "actes-generique", "TdT", $acte_id_e);

        $this->triggerActionOnConnector($s2low['id_ce'], 'recup-reponse-prefecture');

        $this->assertLastMessage('1 réponse de la préfecture a été récupérée.');

        $documents = $this->getInternalAPI()->get('/entite/' . $acte_id_e . '/document?type=actes-reponse-prefecture');
        $reponsePrefectureIdDocument = $documents[0]['id_d'];

        $reponsePrefectureDocument = $this->getDonneesFormulaireFactory()->get($reponsePrefectureIdDocument);

        $this->assertSame(
            sprintf("%s/Document/detail?id_d=%s&id_e=%s", SITE_BASE, $acteDocument['id_d'], $acte_id_e),
            $reponsePrefectureDocument->get('url_acte')
        );

        $acteDocument = $this->getDonneesFormulaireFactory()->get($acteDocument['id_d']);
        $this->assertSame('1', $acteDocument->get('has_reponse_prefecture'));
        $this->assertSame(
            json_encode([
                2 => sprintf("/Document/detail?id_d=%s&id_e=%s", $reponsePrefectureDocument->id_d, $acte_id_e)
            ]),
            $acteDocument->getFileContent('reponse_prefecture_file')
        );
    }
}
