<?php

class XMLFileTest extends PHPUnit\Framework\TestCase
{
    /** @var  XMLFile */
    private $xmlFile;

    protected function setUp()
    {
        parent::setUp();
        $this->xmlFile = new XMLFile();
    }

    public function testGetSimpleXML()
    {
        $xml = $this->xmlFile->getFromFilePath(__DIR__ . "/../fixtures/profil.xml");
        /** @var SimpleXMLElement $element */
        $element = $xml->children(AgapeFile::XSD_SHEMA)[0];
        $this->assertEquals('ArchiveTransfer', (string) $element->attributes()->{'name'});
    }

    public function testGetSimpleXMLFailed()
    {
        $last_xml_errors = false;
        try {
            $this->xmlFile->getFromFilePath(__FILE__);
        } catch (XMLFileException $e) {
            $last_xml_errors = $e->last_xml_errors;
        }
        $this->assertEquals("ParsePI: PI php never end ...\n", $last_xml_errors[0]->{'message'});
    }
}
