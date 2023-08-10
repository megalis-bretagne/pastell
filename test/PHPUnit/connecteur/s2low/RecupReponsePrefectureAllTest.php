<?php

class RecupReponsePrefectureAllTest extends PastellTestCase
{
    use CurlUtilitiesTestTrait;

    public function testRecup()
    {
        $this->mockCurl([
            '/admin/users/api-list-login.php' => true,
            '/modules/actes/api/list_document_prefecture.php' => <<<EOT
[
  {
    "id": "3267",
    "type": "2",
    "related_transaction_id": "3266",
    "number": "201908291002",
    "unique_id": "034-491011698-20190829-201908291002-AI",
    "last_status_id": "21"
  }
]
EOT,
            '/modules/actes/actes_transac_get_document.php?id=3267' =>
                file_get_contents(__DIR__ . '/fixtures/reponse_prefecture.tar.gz'),
            '/modules/actes/api/document_prefecture_mark_as_read.php?transaction_id=3267' => true,
        ]);
        $s2low = $this->createConnector('s2low', 's2low');
        $this->associateFluxWithConnector($s2low['id_ce'], 'actes-reponse-prefecture', 'TdT');

        $documents = $this->getInternalAPI()->get('/entite/' . self::ID_E_COL . '/document?type=actes-reponse-prefecture');
        $this->assertCount(0, $documents);

        $globalConnector = $this->createConnector('s2low', 'S2low', 0);
        $this->triggerActionOnConnector($globalConnector['id_ce'], 'recup-reponse-prefecture');

        $this->assertLastMessage('Résultat :<br/>Bourg-en-Bresse  : 1 réponse de la préfecture a été récupérée.');

        $documents = $this->getInternalAPI()->get('/entite/' . self::ID_E_COL . '/document?type=actes-reponse-prefecture');
        $this->assertCount(1, $documents);
    }

    public function testRecupNothing()
    {
        $this->mockCurl([
            '/admin/users/api-list-login.php' => true,
            '/modules/actes/api/list_document_prefecture.php' => false
        ]);
        $s2low = $this->createConnector('s2low', 's2low');
        $this->associateFluxWithConnector($s2low['id_ce'], 'actes-reponse-prefecture', 'TdT');

        $globalConnector = $this->createConnector('s2low', 'S2low', 0);
        $this->triggerActionOnConnector($globalConnector['id_ce'], 'recup-reponse-prefecture');

        $this->assertLastMessage('Résultat :<br/>Bourg-en-Bresse  : S2low ne retourne pas de Réponse de la préfecture');
    }
}
