<?php

class JSONoutputTest extends PHPUnit\Framework\TestCase
{
    public function testSendJson()
    {
        $jsonOutput = new JSONoutput();
        $this->assertEquals([], json_decode($jsonOutput->getJson([])));
    }


    public function testSendJsonUTF8()
    {
        $jsonOutput = new JSONoutput();
        $this->assertEquals(['école'], json_decode($jsonOutput->getJson(['école'])));
    }

    public function testSendJsonISO()
    {
        $jsonOutput = new JSONoutput();
        $this->assertEquals(null, json_decode($jsonOutput->getJson([utf8_decode('école')])));
    }
}
