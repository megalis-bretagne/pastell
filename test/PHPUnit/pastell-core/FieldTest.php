<?php

class FieldTest extends PHPUnit_Framework_TestCase {

	public function testUnaccent(){
		$this->assertEquals("Eric",Field::unaccent("Éric"));
	}

	public function testCanonicalize(){
		$this->assertEquals("eric",Field::Canonicalize("Éric"));
	}

}