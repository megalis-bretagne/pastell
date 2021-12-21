<?php

class XMLCleanerTest extends PHPUnit\Framework\TestCase
{
    public function compare($input, $expected)
    {
        $xmlCleaner = new XMLCleaner();
        $result = '<?xml version="1.0"?>' . "\n$expected\n";
        $this->assertEquals($result, $xmlCleaner->cleanXML($input));
    }

    public function testRemoveEmptyNode()
    {
        $this->compare("<foo><buz>bar</buz><fitz></fitz></foo>", "<foo><buz>bar</buz></foo>");
    }

    public function testNormalNode()
    {
        $this->compare("<foo>bar</foo>", "<foo>bar</foo>");
    }


    public function testDontRemoveRootNode()
    {
        $this->compare("<foo><buz></buz><fitz></fitz></foo>", "<foo/>");
    }

    public function testRemoveSpaceString()
    {
        $this->compare("<foo><buz>bar</buz><fitz>      </fitz></foo>", "<foo><buz>bar</buz></foo>");
    }

    public function testRemoveEmptyAttribute()
    {
        $this->compare("<foo id=''>bar</foo>", "<foo>bar</foo>");
    }

    public function testNotRemoveNotEmptyAttribute()
    {
        $this->compare('<foo id="12">bar</foo>', '<foo id="12">bar</foo>');
    }

    public function testRemoveTwoAttribute()
    {
        $this->compare("<foo id='' value=''>bar</foo>", "<foo>bar</foo>");
    }

    public function testDontRemoveEmptyChildWithAttributes()
    {
        $this->compare("<foo><buz>bar</buz><fitz id=\"12\"></fitz></foo>", "<foo><buz>bar</buz><fitz id=\"12\"/></foo>");
    }
}
