<?php

use PHPUnit\Framework\TestCase;

class SimpleXMLWrapperTest extends TestCase
{
    /**
     * @var SimpleXMLWrapper
     */
    private $simpleXMLWrapper;

    protected function setUp()
    {
        $this->simpleXMLWrapper = new SimpleXMLWrapper();
    }

    /**
     * @throws SimpleXMLWrapperException
     */
    public function testLoadBadString()
    {
        $this->expectException(SimpleXMLWrapperException::class);
        $this->expectExceptionMessage("XML incorrect");
        $this->simpleXMLWrapper->loadString("foo");
    }

    /**
     * @throws SimpleXMLWrapperException
     */
    public function testLoadString()
    {
        $this->assertEquals("foo", $this->simpleXMLWrapper->loadString("<foo></foo>")->getName());
    }

    /**
     * @throws SimpleXMLWrapperException
     */
    public function testLoadFile()
    {
        $file_path = $this->getFilePath("<foo></foo>");
        $xml = $this->simpleXMLWrapper->loadFile($file_path);
        $this->assertEquals("foo", $xml->getName());
    }

    /**
     * @throws SimpleXMLWrapperException
     */
    public function testLoadBadFile()
    {
        $file_path = $this->getFilePath("foo");
        $this->expectException(SimpleXMLWrapperException::class);
        $this->expectExceptionMessage("Le fichier vfs://test/fichier.xml n'est pas un XML correct");
        $this->simpleXMLWrapper->loadFile($file_path);
    }

    private function getFilePath($file_content)
    {
        org\bovigo\vfs\vfsStream::setup('test');
        $testStreamUrl = org\bovigo\vfs\vfsStream::url('test');
        $file_path = $testStreamUrl . "/fichier.xml";
        file_put_contents($file_path, $file_content);
        return $file_path;
    }
}
