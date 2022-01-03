<?php

class JSONoutputTest extends PHPUnit\Framework\TestCase
{
    public function testSendJson()
    {
        $jsonOutput = new JSONoutput();
        $this->assertEquals(array(), json_decode($jsonOutput->getJson(array())));
    }


    public function testSendJsonUTF8()
    {
        $jsonOutput = new JSONoutput();
        $this->assertEquals(array('école'), json_decode($jsonOutput->getJson(array('école'))));
    }

    public function testSendJsonISO()
    {
        $jsonOutput = new JSONoutput();
        $this->assertEquals(null, json_decode($jsonOutput->getJson(array(utf8_decode('école')))));
    }
}
