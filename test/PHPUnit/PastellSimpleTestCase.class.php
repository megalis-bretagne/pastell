<?php
class PastellSimpleTestCase extends PHPUnit_Framework_TestCase {

	protected function getMockObject($class_name){
		return $this->getMockBuilder($class_name)->disableOriginalConstructor()->getMock();
	}

}