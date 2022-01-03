<?php

use PHPUnit\Framework\TestCase;

class SendFileToBrowserTest extends TestCase
{
    public function testSendFile()
    {
        $sendFileToBrowser = new SendFileToBrowser();
        $this->expectOutputString(file_get_contents(__DIR__ . "/fixtures/sendFileToBrowserExpectedOutput.txt"));
        $sendFileToBrowser->send(__DIR__ . "/fixtures/test.yml");
    }

    public function testSendData()
    {
        $sendFileToBrowser = new SendFileToBrowser();
        $this->expectOutputString(file_get_contents(__DIR__ . "/fixtures/sendDataToBrowserExpectedOutput.txt"));
        $sendFileToBrowser->sendData("foo", "foo.txt", "text/plain");
    }
}
