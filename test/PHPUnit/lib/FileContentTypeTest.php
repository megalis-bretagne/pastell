<?php

class FileContentTypeTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var FileContentType
     */
    private $mimeCode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mimeCode = new FileContentType();
    }

    public function testTextFile()
    {
        $this->assertEquals("text/plain", $this->mimeCode->getContentType(__DIR__ . "/fixtures/autorite-cert.pem"));
    }

    public function testNotExistingFile()
    {
        $this->assertFalse($this->mimeCode->getContentType(__DIR__ . "/foo"));
    }

    public function testZipFile()
    {
        $this->assertEquals("application/zip", $this->mimeCode->getContentType(__DIR__ . "/fixtures/test.zip"));
    }

    public function testLibreOfficeFile()
    {
        $this->assertEquals(
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            $this->mimeCode->getContentType(__DIR__ . "/fixtures/test.docx")
        );
    }
}
