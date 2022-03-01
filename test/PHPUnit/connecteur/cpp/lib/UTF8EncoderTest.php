<?php

class UTF8EncoderTest extends ExtensionCppTestCase
{
    /** @var  UTF8Encoder */
    private $utf8Encoding;

    private $testArray;

    protected function setUp(): void
    {
        parent::setUp();
        $this->utf8Encoding = new UTF8Encoder();
        $this->testArray = array(
            'foo' => 'bar',
            'baz' => array(
                'pim' => "0000012",
                'pam' => 42,
                'poum' => 'toto',
                'toto' => 'école'
            )
        );
    }

    public function testEncode()
    {
        $result = $this->utf8Encoding->encode($this->testArray);
        $this->assertIsInt($result['baz']['pam']);
        $this->assertIsString($result['baz']['pim']);
        $this->assertEquals('Ã©cole', $result['baz']['toto']);
    }
}
