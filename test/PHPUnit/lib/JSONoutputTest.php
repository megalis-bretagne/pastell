<?php

class JSONoutputTest extends PHPUnit\Framework\TestCase {

	public function testSendJson(){
		$jsonOutput = new JSONoutput();
		$this->assertEquals(array(),json_decode($jsonOutput->getJson(array())));
	}

}