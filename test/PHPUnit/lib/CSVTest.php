<?php

class CSVTest extends PHPUnit_Framework_TestCase {

	public function testGet(){
		$csv = new CSV();
		$data = $csv->get(PASTELL_PATH."/data-exemple/agent.csv");
		$this->assertEquals("Grigorov",$data[0][4]);
	}

	public function testGetZip(){
		$csv = new CSV();
		$data = $csv->get(__DIR__."/fixtures/agent.csv.gz");
		$this->assertEquals("Grigorov",$data[0][4]);
	}

	public function testEmpty(){
		$csv = new CSV();
		$this->assertEmpty($csv->get("foo/bar"));
	}


}