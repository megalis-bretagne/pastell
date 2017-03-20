<?php

class JSONoutputTest extends PHPUnit_Framework_TestCase {

	public function testSendJson(){
		$jsonOutput = new JSONoutput();
		$this->assertRegExp("#\[.?\]#",$jsonOutput->getJson(array()));
	}

}