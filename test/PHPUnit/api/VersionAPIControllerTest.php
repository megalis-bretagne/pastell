<?php


class VersionAPIControllerTest extends PastellTestCase {

	public function testGet(){
		$info = $this->getInternalAPI()->get("version");
		$this->assertEquals('1.4-fixtures',$info['version']);
	}

}