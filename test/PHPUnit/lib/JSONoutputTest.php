<?php

class JSONoutputTest extends PHPUnit_Framework_TestCase {

	public function testDisplayError(){

		$jsonOutput = new JSONoutput();
		$this->setExpectedException("Exception","Exit called with code 0");
		$this->expectOutputString('Content-type: text/plain
{"status":"error","error-message":"MESSAGE ERREUR"}');
		$jsonOutput->displayErrorAndExit("MESSAGE ERREUR");


	}

}