<?php

class PastellTimerTest extends PHPUnit\Framework\TestCase {
	
	public function testAll(){
		$timer = new PastellTimer();
		//Ca échoue parfois elapsedtime est trop proche de 0 !
		usleep(2);
		$this->assertTrue($timer->getElapsedTime() > 0);
	}
}