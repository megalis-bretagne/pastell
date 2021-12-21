<?php

class Array2XMLTest extends PHPUnit\Framework\TestCase
{
    private function getSimpleXML(array $array_to_test)
    {
        $array2XML = new Array2XML();
        return simplexml_load_string($array2XML->getXML("foo", $array_to_test));
    }

    public function testGetXMLEmpty()
    {
        $this->assertEquals("foo", $this->getSimpleXML(array())->getName());
    }

    public function testGetOneTag()
    {
        $xml = $this->getSimpleXML(array("bar" => "baz"));
        $this->assertEquals("baz", $xml->{'bar'});
    }

    public function testGetOneTagNum()
    {
        $xml = $this->getSimpleXML(array("baz"));
        $this->assertEquals("baz", $xml->{'foo'});
    }

    public function testNeestadArray()
    {
        $xml = $this->getSimpleXML(array("bar" => array("fizz" => "buzz")));
        $this->assertEquals("buzz", $xml->{'bar'}->{'fizz'});
    }
}
