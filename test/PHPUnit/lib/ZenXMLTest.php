<?php

class ZenXMLTest extends PHPUnit\Framework\TestCase
{
    private function getSimpleXML(ZenXML $zenXML)
    {
        return simplexml_load_string($zenXML->asXML());
    }

    public function testOneElement()
    {
        $zenXML = new ZenXML("foo");
        $simpleXML = $this->getSimpleXML($zenXML);
        $this->assertEquals("foo", $simpleXML->getName());
    }

    public function testGetCDATA()
    {
        $zenXML = new ZenXML("foo", "bar");
        $simpleXML = $this->getSimpleXML($zenXML);
        $this->assertEquals("bar", strval($simpleXML));
    }

    /* Il faut impérativement échapper les caractère spéciaux, nottamment &  */
    public function testGetCDATAWithSpecialChar()
    {
        $zenXML = new ZenXML("foo", "bar & baz", true);
        $simpleXML = $this->getSimpleXML($zenXML);
        $this->assertEquals("bar & baz", strval($simpleXML));
    }

    public function testSetNode()
    {
        $zenXML = new ZenXML("foo");
        $zenXML->{'bar'} = 'baz';
        $simpleXML = $this->getSimpleXML($zenXML);
        $this->assertEquals("baz", strval($simpleXML->{'bar'}));
    }

    public function testSetAttribute()
    {
        $zenXML = new ZenXML("foo");
        $zenXML->{'bar'}['baz'] = 'bazzz';
        $simpleXML = $this->getSimpleXML($zenXML);
        $this->assertEquals("bazzz", strval($simpleXML->{'bar'}['baz']));
    }

    public function testSetAttributeWithEscapeString()
    {
        $zenXML = new ZenXML("foo", "", true);
        $zenXML->{'bar'}['baz'] = 'aaaa & bazzz';
        $simpleXML = $this->getSimpleXML($zenXML);
        $this->assertEquals("aaaa & bazzz", strval($simpleXML->{'bar'}['baz']));
    }

    public function testSetNodeByObject()
    {
        $foo = new ZenXML("foo");
        $bar = new ZenXML("bar", "baz");
        $foo->bar = $bar;
        $simpleXML = $this->getSimpleXML($foo);
        $this->assertEquals("baz", strval($simpleXML->{'bar'}));
    }

    public function testMultiValued()
    {
        $foo = new ZenXML("foo");
        $foo->bar[0] = "baz";
        $foo->bar[1] = "bazz";
        $simpleXML = $this->getSimpleXML($foo);
        $this->assertEquals("bazz", $simpleXML->{'bar'}[1]);
    }

    public function testMultiValuedNotIndexed()
    {
        $foo = new ZenXML("foo");
        $foo->bar[] = "baz";
        $foo->bar[] = "bazz";
        $simpleXML = $this->getSimpleXML($foo);
        $this->assertEquals("bazz", $simpleXML->{'bar'}[1]);
    }

    public function testMultiValuedNode()
    {
        $foo = new ZenXML("foo");
        $foo->bar[0] = new ZenXML("bar", "baz");
        $foo->bar[1] = new ZenXML("bar", "bazz");
        $simpleXML = $this->getSimpleXML($foo);
        $this->assertEquals("bazz", $simpleXML->{'bar'}[1]);
    }

    public function testOffsetExists()
    {
        $foo = new ZenXML("foo");
        $foo['bar'] = 'baz';
        $this->assertFalse($foo->offsetExists(2));
        $this->assertTrue($foo->offsetExists('bar'));
    }

    public function testOffsetGet()
    {
        $foo = new ZenXML("foo");
        $foo['bar'] = 'baz';
        $this->assertEquals("baz", $foo->offsetGet('bar'));
    }

    public function testOffsetGetInt()
    {
        $foo = new ZenXML("foo");
        $foo->bar[0] = "baz";
        $foo->bar[1] = "bazz";
        $this->assertEquals("<bar>bazz</bar>\n", $foo->{'bar'}->offsetGet(1)->asXML());
    }

    public function testOffsetGetIntNotExists()
    {
        $foo = new ZenXML("foo");
        $foo->bar[0] = "baz";
        $this->assertEquals("<bar></bar>\n", $foo->{'bar'}->offsetGet(1)->asXML());
    }

    public function testOffsetUnset()
    {
        $foo = new ZenXML("foo");
        $foo['bar'] = 'baz';
        $foo->offsetUnset('bar');
        $simpleXML = $this->getSimpleXML($foo);
        $this->assertEmpty($simpleXML->attributes());
    }
}
