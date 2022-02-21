<?php

class DocumentSQLTest extends PastellTestCase
{
    /**
     * @var DocumentSQL
     */
    private $documentSql;

    protected function setUp(): void
    {
        $this->documentSql = $this->getObjectInstancier()->getInstance(DocumentSQL::class);
        parent::setUp();
    }

    public function testGetDocumentsLastActionByTypeEntityAndCreationDate(): void
    {
        $doc = $this->createDocument('test');

        $documents = $this->documentSql->getDocumentsLastActionByTypeEntityAndCreationDate(
            self::ID_E_COL,
            'test',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 day'))
        );

        $this->assertSame($doc['id_d'], $documents[0]['id_d']);
    }
}
