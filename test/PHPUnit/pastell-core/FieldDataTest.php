<?php

class FieldDataTest extends PHPUnit\Framework\TestCase {

	public function testSetValue(){
		$field = new Field('test',['depend'=>true,"type"=>"select","value"=>[1=>"pim","pam","poum"]]);
		$fieldData = new FieldData($field,["test"=>0]);
		$this->assertEquals(["test: non défini"],$fieldData->getValue());
	}

}