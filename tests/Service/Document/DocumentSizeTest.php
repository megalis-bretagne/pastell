<?php

namespace Pastell\Tests\Service\Document;

use Exception;
use NotFoundException;
use Pastell\Service\Document\DocumentSize;
use PastellTestCase;

class DocumentSizeTest extends PastellTestCase
{
    /**
     * @var DocumentSize
     */
    private $documentSize;

    protected function setUp()
    {
        $this->documentSize = $this->getObjectInstancier()->getInstance(DocumentSize::class);
        parent::setUp();
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testGetDocumentSize(): void
    {
        $document = $this->createDocument('test');
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);

        $donneesFormulaire->addFileFromData('fichier', 'file.txt', '1234');

        $this->assertSame(247, $this->documentSize->getSize($document['id_d']));
        $donneesFormulaire->addFileFromData('fichier_simple', 'file.txt', '1234');
        $this->assertSame(284, $this->documentSize->getSize($document['id_d']));
    }

    public function humanReadableSizeProvider(): iterable
    {
        yield [0, '0B'];
        yield [1, '1B'];
        yield [1000, '0.98kB'];
        yield [1024, '1kB'];
        yield [239775753, '228.67MB'];
        yield [3333333333, '3.1GB'];
    }

    /**
     * @dataProvider humanReadableSizeProvider
     */
    public function testGetHumanReadableSize(int $size, string $expected): void
    {
        $this->assertSame($expected, $this->documentSize->getHumanReadableSize($size));
    }
}
