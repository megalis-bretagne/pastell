<?php
class PastellSimpleTestCase extends PHPUnit\Framework\TestCase {

	protected function getMockObject($class_name){
		return $this->getMockBuilder($class_name)->disableOriginalConstructor()->getMock();
	}

}