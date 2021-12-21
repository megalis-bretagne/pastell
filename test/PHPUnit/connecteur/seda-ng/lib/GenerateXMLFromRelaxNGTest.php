<?php

use PHPUnit\Framework\TestCase;

class GenerateXMLFromRelaxNGTest extends TestCase
{
    /** @var  SimpleXMLElement */
    private $relaxNg;

    /** @var  GenerateXMLFromRelaxNg */
    private $relaxNgGenerateXML;

    protected function setUp()
    {
        $relaxNG = new RelaxNG();
        $this->relaxNg = $relaxNG->getFromFilePath(__DIR__ . "/../fixtures/grammar-test.rng");
        $this->relaxNgGenerateXML = new GenerateXMLFromRelaxNg(new RelaxNG());
    }

    private function getDocumentElementFromTest($elementName)
    {
        $inputNode = $this->relaxNg->xpath("//rng:element[@name='$elementName']")[0];
        $domDocument = new DOMDocument();
        $this->relaxNgGenerateXML->generate($inputNode, $domDocument);
        $domElement = $domDocument->documentElement;
        $this->assertEquals($elementName, $domElement->nodeName);
        return $domElement;
    }

    public function testEmptyElement()
    {
        $this->getDocumentElementFromTest("TestEmptyElement");
    }

    public function testTextElement()
    {
        $domElement = $this->getDocumentElementFromTest("TestTextElement");
        $this->assertEquals("Texte de test", $domElement->nodeValue);
    }

    public function testRefElement()
    {
        $domElement = $this->getDocumentElementFromTest("TestRefElement");
        $this->assertEquals("TestElementReference", $domElement->childNodes->item(0)->nodeName);
    }

    public function testDataElement()
    {
        $domElement = $this->getDocumentElementFromTest("TestDataElement");
        $this->assertEmpty($domElement->nodeValue);
    }

    public function testAttributeElement()
    {
        $domElement = $this->getDocumentElementFromTest("TestAttributeElement");
        $this->assertEquals('AttributeValue', $domElement->getAttribute("AttributeKey"));
    }

    public function testBigFile()
    {
        $relaxng_path = __DIR__ . "/../fixtures/EMEG_PROFIL_PES_0002_v1_schema.rng";
        $xml = $this->relaxNgGenerateXML->generateFromRelaxNG($relaxng_path);
        $sedaValidation = new SedaValidation();
        $result = $sedaValidation->validateRelaxNG($xml, $relaxng_path);
        $last_errors = $sedaValidation->getLastErrors();
        $this->assertEmpty($last_errors);
        $this->assertTrue($result);
    }

    public function testError()
    {
        $domDocument = new DOMDocument();
        $xmlFile = new XMLFile();

        $xml = $xmlFile->getFromString("<toto><b/></toto>");

        $this->expectException("Exception");
        $this->expectExceptionMessage("Unkown « toto » tag in RelaxNG");
        $this->relaxNgGenerateXML->generate($xml, $domDocument);
    }
}
