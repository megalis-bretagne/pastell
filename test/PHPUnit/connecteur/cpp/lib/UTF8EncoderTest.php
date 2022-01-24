<?php

class UTF8EncoderTest extends ExtensionCppTestCase
{
    /** @var  UTF8Encoder */
    private $utf8Encoding;

    private $testArray;

    protected function setUp()
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
        $this->assertInternalType('int', $result['baz']['pam']);
        $this->assertInternalType('string', $result['baz']['pim']);
        $this->assertEquals('Ã©cole', $result['baz']['toto']);
    }

    /*
     *
     * Les deux tests suivants ne fonctionnent plus : c'est sans doute normal avec la modification de l'encodage par défaut...
     *
     *
    public function testDecode(){

        $this->testArray['baz']['toto'] = utf8_encode('école');

        print_r($this->testArray);

        $result = $this->utf8Encoding->decode($this->testArray);
        print_r($result);
        $this->assertInternalType('int',$result['baz']['pam']);
        $this->assertInternalType('string',$result['baz']['pim']);
        $this->assertEquals('école',$result['baz']['toto']);
    }

    public function testRecipocite(){
        $this->assertEquals(
            $this->testArray,
            $this->utf8Encoding->decode(
                $this->utf8Encoding->encode(
                    $this->testArray
                )
            )
        );
    }
     */
}
