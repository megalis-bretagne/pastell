<?php

namespace Connector\Ensap;

use Pastell\Connector\Ensap\XmlEnveloppeValidator;
use PastellTestCase;

class XmlEnveloppeValidatorTest extends PastellTestCase
{
    private XmlEnveloppeValidator $validator;
    private string $xml;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->validator = $this->getObjectInstancier()->getInstance(XmlEnveloppeValidator::class);
        $this->xml = file_get_contents(__DIR__ . '/fixtures/xml/exemple.xml');
    }

    public function testValidateXsd(): void
    {
        self::assertTrue($this->validator->validateXsd($this->xml));
    }

    public function testValidateContent(): void
    {
        self::assertTrue($this->validator->validateContent($this->xml));
    }
}
