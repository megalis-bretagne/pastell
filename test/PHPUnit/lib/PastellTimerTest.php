<?php

class PastellTimerTest extends PHPUnit\Framework\TestCase {
	
	public function testAll(){
		$timer = new PastellTimer();
		$this->assertTrue($timer->getElapsedTime() > 0);
	}
}