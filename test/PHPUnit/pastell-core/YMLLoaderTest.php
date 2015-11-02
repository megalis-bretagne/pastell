<?php
require_once __DIR__.'/../init.php';

class YMLLoaderTest extends PHPUnit_Framework_TestCase {
	
	public function testFileNotFound(){
		$ymlLoader = new YMLLoader(new MemoryCacheNone());
		$this->assertFalse($ymlLoader->getArray("file does not exists"));
	}
	
	
	
	
}