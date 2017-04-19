<?php

class PastellTimerTest extends PHPUnit_Framework_TestCase {
	
	public function testAll(){
		$timer = new PastellTimer();
		$this->assertTrue($timer->getElapsedTime() > 0);
	}
}