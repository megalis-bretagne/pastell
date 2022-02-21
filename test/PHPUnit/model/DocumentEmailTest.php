<?php

class DocumentEmailTest extends PastellTestCase
{
    /**
     * @var DocumentEmail
     */
    private $documentEmail;

    protected function setUp(): void
    {
        $this->documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
        parent::setUp();
    }

    public function testGetNumberOfMailRead(): void
    {
        $documentId = 'id_d';
        $key1 = $this->documentEmail->add($documentId, '1@example.org', 'to');
        $key2 = $this->documentEmail->add($documentId, '2@example.org', 'to');

        $this->assertSame(0, $this->documentEmail->getNumberOfMailRead($documentId));

        $this->documentEmail->consulter($key1, $this->getJournal());
        $this->assertSame(1, $this->documentEmail->getNumberOfMailRead($documentId));
        $this->documentEmail->consulter($key2, $this->getJournal());
        $this->assertSame(2, $this->documentEmail->getNumberOfMailRead($documentId));
    }
}
