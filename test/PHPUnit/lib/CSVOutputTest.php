<?php

class CSVOutputTest extends PHPUnit\Framework\TestCase
{
    public function testDisplay()
    {
        $csvOutput  = new CSVoutput();
        $this->expectOutputString("foo;bar\n");
        $csvOutput->display([["foo","bar"]]);
    }

    public function testSpecificPastellStuff()
    {
        $csvOutput  = new CSVoutput();
        $this->expectOutputString("baz\n");
        $csvOutput->display([["preuve" => "foo","bar" => "baz"]]);
    }

    public function testReplaceCRLFWithSpaceInMessage()
    {
        $csvOutput  = new CSVoutput();
        $this->expectOutputString('"foo bar";"fizz buzz"' . "\n");
        $csvOutput->display([["message" => "foo\nbar","message_horodate" => "fizz\nbuzz"]]);
    }

    public function testDisableHeader()
    {
        $csvOutput  = new CSVoutput();
        $csvOutput->disableHeader();
        $this->expectOutputString("foo\n");
        $csvOutput->send("foo.csv", [["foo"]]);
    }

    public function testSend()
    {
        $csvOutput  = new CSVoutput();
        $this->expectOutputRegex("#foo#");
        $csvOutput->sendAttachment("foo.csv", [["foo"]]);
    }
}
