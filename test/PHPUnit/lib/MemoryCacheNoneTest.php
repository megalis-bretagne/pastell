<?php

class MemoryCacheNoneTest extends PHPUnit_Framework_TestCase {


	public function testStore(){
		$memoryCacheNone = new MemoryCacheNone();
		$memoryCacheNone->store("foo","bar");
	}

	public function testFetch(){
		$memoryCacheNone = new MemoryCacheNone();
		$this->assertFalse($memoryCacheNone->fetch("foo"));
	}

	public function testDelete(){
		$memoryCacheNone = new MemoryCacheNone();
		$memoryCacheNone->delete("foo");
	}
}