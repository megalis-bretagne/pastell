<?php

namespace Pastell\Tests\Connector\Ensap;

use InvalidArgumentException;
use Exception;
use Pastell\Connector\Ensap\PdfEnveloppeValidator;
use PastellTestCase;
use RuntimeException;

class PdfEnveloppeValidatorTest extends PastellTestCase
{
    private PdfEnveloppeValidator $validator;
    private array $files;
    private string $xml;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->validator = $this->getObjectInstancier()->getInstance(PdfEnveloppeValidator::class);
        $this->files = array_map('basename', glob(__DIR__ . '/fixtures/pdf/*'));
        $this->xml = file_get_contents(__DIR__ . '/fixtures/xml/exemple.xml');
    }

    /**
     * @throws Exception
     */
    public function testValidatePdf(): void
    {
        self::assertTrue($this->validator->validatePdfFiles($this->files, $this->xml));
    }

    /**
     * @throws Exception
     */
    public function testValidatePdfFilesWithDuplicateFileReferenceInXml(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Le fichier 1641275074060_13000920601211_BPaie_01092021.pdf est déjà référencé dans le XML'
        );

        $this->xml = str_replace(
            '<nom_fichier>1870194035088_13000920600010_BPaie_01092021.pdf</nom_fichier>',
            '<nom_fichier>1641275074060_13000920601211_BPaie_01092021.pdf</nom_fichier>',
            $this->xml
        );

        $this->validator->validatePdfFiles($this->files, $this->xml);
    }

    /**
     * @throws Exception
     */
    public function testValidatePdfFilesWithFileNotReferencedInXml(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le fichier non_existant.pdf n'est pas référencé dans le XML");

        $this->files[] = 'non_existant.pdf';
        $this->validator->validatePdfFiles($this->files, $this->xml);
    }

    /**
     * @throws Exception
     */
    public function testValidatePdfFilesWithFileNotInProvidedFileList(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Le fichier 1870194035088_13000920600010_BPaie_01092021.pdf n'est pas dans la liste de fichiers fournie"
        );

        if (($key = array_search('1870194035088_13000920600010_BPaie_01092021.pdf', $this->files, true)) !== false) {
            unset($this->files[$key]);
        }
        $this->validator->validatePdfFiles($this->files, $this->xml);
    }
}
